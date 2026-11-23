<?php

function profileEmptyStats(): array
{
    return ['longest' => 0, 'total' => 0, 'count' => 0, 'keyholder' => 0, 'member_days' => 0];
}

function profileTs($v): ?int
{
    if ($v === null || $v === '') return null;
    if (ctype_digit((string)$v)) return (int)$v;
    $t = strtotime((string)$v);
    return $t === false ? null : $t;
}

function profileFormatDuration(int $seconds): string
{
    $d = intdiv($seconds, 86400);
    $h = intdiv($seconds % 86400, 3600);
    $m = intdiv($seconds % 3600, 60);
    $parts = [];
    if ($d > 0) $parts[] = $d . 'd';
    if ($h > 0) $parts[] = $h . 'h';
    if ($m > 0 || !$parts) $parts[] = $m . 'm';
    return implode(' ', array_slice($parts, 0, 2));
}

function profileMemberDays(?string $createdAt): int
{
    $t = profileTs($createdAt);
    return $t === null ? 0 : max(0, intdiv(time() - $t, 86400));
}

/**
 * Lock statistics per box, derived from box_data_history.
 * Returns box_id => ['longest' => sec, 'total' => sec, 'count' => n]
 * A lock ends when the box reports open, or when its timer ran out.
 */
function profileStatsByBox(PDO $pdo, array $boxIds): array
{
    if (!$boxIds) return [];
    $ph   = implode(',', array_fill(0, count($boxIds), '?'));
    $stmt = $pdo->prepare("SELECT box_id, lock_status, open_time, protection_level_timer, created_at
                           FROM box_data_history WHERE box_id IN ($ph)
                           ORDER BY box_id, created_at ASC, id ASC");
    $stmt->execute(array_values($boxIds));

    $rowsByBox = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $rowsByBox[$r['box_id']][] = $r;

    $now = time();
    $out = [];
    foreach ($rowsByBox as $boxId => $rows) {
        $longest = 0; $total = 0; $count = 0;
        $lockStart = null; $curEnd = null;
        $close = function (int $end) use (&$lockStart, &$longest, &$total) {
            $dur = max(0, $end - $lockStart);
            $longest = max($longest, $dur);
            $total  += $dur;
            $lockStart = null;
        };

        foreach ($rows as $r) {
            $t = profileTs($r['created_at']);
            if ($t === null) continue;
            $locked   = (int)$r['lock_status'] === 1;
            $timerEnd = !empty($r['protection_level_timer']) ? profileTs($r['open_time']) : null;

            if ($lockStart !== null) {
                if ($curEnd !== null && $curEnd <= $t) {      // timer ran out before this event
                    $close($curEnd);
                } elseif (!$locked) {                          // opened
                    $close($t);
                } else {                                       // still locked (e.g. time extended)
                    $curEnd = $timerEnd;
                    continue;
                }
            }
            if ($locked && $lockStart === null) {
                $lockStart = $t; $curEnd = $timerEnd; $count++;
            }
        }
        if ($lockStart !== null) {
            $close(($curEnd !== null && $curEnd <= $now) ? $curEnd : $now);
        }
        $out[$boxId] = ['longest' => $longest, 'total' => $total, 'count' => $count];
    }
    return $out;
}

function profileAggregateStats(array $list): array
{
    $a = ['longest' => 0, 'total' => 0, 'count' => 0];
    foreach ($list as $s) {
        $a['longest'] = max($a['longest'], $s['longest']);
        $a['total']  += $s['total'];
        $a['count']  += $s['count'];
    }
    return $a;
}

/** In how many foreign boxes is this user the (last) keyholder? lowercase name => count */
function profileKeyholderCounts(PDO $pdo, array $usernames): array
{
    if (!$usernames) return [];
    $ph   = implode(',', array_fill(0, count($usernames), '?'));
    $stmt = $pdo->prepare("SELECT bd.keyholder AS name, COUNT(DISTINCT bd.box_id) AS c
                           FROM box_details bd
                           JOIN user_boxes ub ON ub.box_id = bd.box_id
                           JOIN users ku ON ku.username = bd.keyholder
                           WHERE bd.keyholder IN ($ph) AND ub.user_id <> ku.id
                           GROUP BY bd.keyholder");
    $stmt->execute(array_values($usernames));
    $out = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $out[strtolower($r['name'])] = (int)$r['c'];
    return $out;
}

/** Stats for one user (own profile, share page). */
function profileStatsForUser(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT username, created_at FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) return profileEmptyStats();

    $stmt = $pdo->prepare('SELECT box_id FROM user_boxes WHERE user_id = ?');
    $stmt->execute([$userId]);
    $boxIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $agg = profileAggregateStats(profileStatsByBox($pdo, $boxIds));
    $kh  = profileKeyholderCounts($pdo, [$u['username']])[strtolower($u['username'])] ?? 0;
    return $agg + ['keyholder' => $kh, 'member_days' => profileMemberDays($u['created_at'])];
}

/**
 * Stats for many users at once (lockees.php).
 * $publicBoxes rows need: user_id, box_id, username, member_since
 * Returns user_id => stats
 */
function profileStatsForPublicBoxes(PDO $pdo, array $publicBoxes): array
{
    $byBox = profileStatsByBox($pdo, array_column($publicBoxes, 'box_id'));
    $perUser = []; $names = []; $since = [];
    foreach ($publicBoxes as $b) {
        $uid = (int)$b['user_id'];
        $perUser[$uid][] = $byBox[$b['box_id']] ?? ['longest' => 0, 'total' => 0, 'count' => 0];
        $names[$uid] = $b['username'];
        $since[$uid] = $b['member_since'] ?? null;
    }
    $kh  = profileKeyholderCounts($pdo, array_values($names));
    $out = [];
    foreach ($perUser as $uid => $list) {
        $out[$uid] = profileAggregateStats($list) + [
            'keyholder'   => $kh[strtolower($names[$uid])] ?? 0,
            'member_days' => profileMemberDays($since[$uid]),
        ];
    }
    return $out;
}

/**
 * Badge definitions. group => tiers in ascending order.
 * Only the highest earned tier of each group is shown.
 * To add a badge: add one line.
 */
function profileBadgeDefs(): array
{
    $day = 86400;
    return [
        'longest' => [
            ['icon' => '🔒', 'label' => '24 Hours',  'desc' => 'Longest lock: 1 day',    'test' => fn($s) => $s['longest'] >= 1 * $day],
            ['icon' => '🔒', 'label' => '7 Days',    'desc' => 'Longest lock: 7 days',   'test' => fn($s) => $s['longest'] >= 7 * $day],
            ['icon' => '⛓️', 'label' => '30 Days',   'desc' => 'Longest lock: 30 days',  'test' => fn($s) => $s['longest'] >= 30 * $day],
            ['icon' => '🏆', 'label' => '100 Days',  'desc' => 'Longest lock: 100 days', 'test' => fn($s) => $s['longest'] >= 100 * $day],
            ['icon' => '👑', 'label' => '1 Year',    'desc' => 'Longest lock: 365 days', 'test' => fn($s) => $s['longest'] >= 365 * $day],
        ],
        'locks' => [
            ['icon' => '🔑', 'label' => 'First Lock', 'desc' => 'Completed a first lock', 'test' => fn($s) => $s['count'] >= 1],
            ['icon' => '🔑', 'label' => '10 Locks',   'desc' => '10 locks',               'test' => fn($s) => $s['count'] >= 10],
            ['icon' => '🔑', 'label' => '50 Locks',   'desc' => '50 locks',               'test' => fn($s) => $s['count'] >= 50],
        ],
        'keyholder' => [
            ['icon' => '🗝️', 'label' => 'Keyholder',     'desc' => 'Holds the key for another lockee', 'test' => fn($s) => $s['keyholder'] >= 1],
            ['icon' => '🗝️', 'label' => 'Key Collector', 'desc' => 'Holds keys for 3+ lockees',        'test' => fn($s) => $s['keyholder'] >= 3],
        ],
        'member' => [
            ['icon' => '🌱', 'label' => 'Member 30 Days', 'desc' => 'Member for 30 days', 'test' => fn($s) => $s['member_days'] >= 30],
            ['icon' => '🌳', 'label' => 'Member 1 Year',  'desc' => 'Member for a year',  'test' => fn($s) => $s['member_days'] >= 365],
        ],
    ];
}

function profileEarnedBadges(array $stats): array
{
    $stats += profileEmptyStats();
    $out = [];
    foreach (profileBadgeDefs() as $tiers) {
        $best = null;
        foreach ($tiers as $t) { if ($t['test']($stats)) $best = $t; }
        if ($best) $out[] = $best;
    }
    return $out;
}