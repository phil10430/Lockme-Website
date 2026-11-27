<?php

session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/profile_helpers.php';
require_once __DIR__ . '/includes/profile_view.php';

$bodyClass = "community-page";

// Return history only for a box that is currently public.
if (isset($_GET['history_box'])) {
    header('Content-Type: application/json; charset=utf-8');
    $historyBoxId = trim((string) $_GET['history_box']);

    try {
        $publicCheck = $pdo->prepare("SELECT 1 FROM user_boxes ub JOIN user_details ud ON ud.user_id = ub.user_id WHERE ub.box_id = :box_id AND ud.profile_visibility = 'public' LIMIT 1");
        $publicCheck->execute(['box_id' => $historyBoxId]);

        if (!$publicCheck->fetchColumn()) {
            http_response_code(404);
            echo json_encode(['error' => 'Public profile not found.']);
            exit;
        }

        $historyStmt = $pdo->prepare("SELECT lock_status, open_time, created_at, protection_level_timer, protection_level_password
                                      FROM box_data_history WHERE box_id = :box_id
                                      ORDER BY created_at DESC LIMIT 1000");
        $historyStmt->execute(['box_id' => $historyBoxId]);
        echo json_encode(['box_id' => $historyBoxId, 'history' => $historyStmt->fetchAll(PDO::FETCH_ASSOC)], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Unable to load history.']);
    }
    exit;
}

require_once __DIR__ . '/templates/header.php';

// =========================================================
// HELPER FUNCTIONS
// =========================================================

function community_box_state(?array $status): string
{
    if ($status === null) return 'unknown';
    $value = strtolower((string) $status['lock_status']);
    if (in_array($value, ['1', 'locked', 'true', 'closed'], true)) return 'locked';
    if (in_array($value, ['0', 'unlocked', 'false', 'open'], true)) return 'unlocked';
    return 'unknown';
}

function community_is_effectively_locked(?array $status): bool
{
    if (community_box_state($status) !== 'locked') return false;
    if (!empty($status['protection_level_timer']) && !empty($status['open_time'])) {
        $openTime = strtotime($status['open_time']);
        if ($openTime !== false && $openTime <= time()) return false;
    }
    return true;
}

function community_format_duration(int $seconds): string
{
    $days    = intdiv($seconds, 86400);
    $hours   = intdiv($seconds % 86400, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    $parts = [];
    if ($days > 0)  $parts[] = $days . 'd';
    if ($hours > 0) $parts[] = $hours . 'h';
    if ($minutes > 0 || empty($parts)) $parts[] = $minutes . 'm';
    return implode(' ', $parts);
}

function community_locked_duration(?string $lockedSince): string
{
    if (!$lockedSince) return '';
    $timestamp = strtotime($lockedSince);
    if ($timestamp === false) return '';
    return community_format_duration(max(0, time() - $timestamp));
}

function community_relative_time(?string $date): string
{
    if (!$date) return '';
    $timestamp = strtotime($date);
    if ($timestamp === false) return '';
    $diff = max(0, time() - $timestamp);

    if ($diff < 60) return 'just now';
    if ($diff < 3600) {
        $m = intdiv($diff, 60);
        return $m . ($m === 1 ? ' minute ago' : ' minutes ago');
    }
    if ($diff < 86400) {
        $h = intdiv($diff, 3600);
        return $h . ($h === 1 ? ' hour ago' : ' hours ago');
    }
    if ($diff < 604800) {
        $d = intdiv($diff, 86400);
        return $d . ($d === 1 ? ' day ago' : ' days ago');
    }
    return date('M j, Y', $timestamp);
}

// =========================================================
// ALL PUBLIC PROFILES (one row per user x box; users without a box included)
// =========================================================
$stmt = $pdo->prepare("
    SELECT u.id AS user_id, u.username, u.pro_version, u.created_at AS member_since,
           ub.box_id, ub.registered_at,
           ud.avatar_path, ud.birth_date,
           ud.social_x, ud.social_instagram, ud.social_bluesky,
           " . profileFieldColumnsSql('ud') . "
    FROM users u
    JOIN user_details ud ON ud.user_id = u.id
    LEFT JOIN user_boxes ub ON ub.user_id = u.id
    WHERE ud.profile_visibility = 'public'
    ORDER BY ub.registered_at DESC, u.username ASC
");
$stmt->execute();
$publicRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by user
$members = [];
foreach ($publicRows as $row) {
    $uid = (int) $row['user_id'];
    if (!isset($members[$uid])) {
        $members[$uid] = $row;
        $members[$uid]['boxes'] = [];
    }
    if ($row['box_id'] !== null && $row['box_id'] !== '') {
        $members[$uid]['boxes'][] = (string) $row['box_id'];
    }
}
$publicUserCount = count($members);

$boxIds = [];
$ownersByBox = [];
$memberByName = []; // lowercase username => user_id (fallback for old keyholder entries)
foreach ($members as $uid => $m) {
    $memberByName[strtolower($m['username'])] = $uid;
    foreach ($m['boxes'] as $bid) {
        $boxIds[] = $bid;
        $ownersByBox[$bid] = $m;
    }
}

// =========================================================
// LOAD BOX HISTORY (max. 10 entries per box)
// =========================================================
$historyByBox = [];

if (!empty($boxIds)) {
    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));
    $stmt = $pdo->prepare("
        SELECT box_id, lock_status, open_time, created_at, protection_level_timer
        FROM box_data_history
        WHERE box_id IN ($placeholders)
        ORDER BY created_at DESC
    ");
    $stmt->execute($boxIds);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $history) {
        $boxName = (string) $history['box_id'];
        if (!isset($historyByBox[$boxName])) $historyByBox[$boxName] = [];
        if (count($historyByBox[$boxName]) < 10) $historyByBox[$boxName][] = $history;
    }
}

$latestStatus = [];
foreach ($historyByBox as $boxId => $history) {
    if (!empty($history)) $latestStatus[(string) $boxId] = $history[0];
}

// =========================================================
// BOX-SPECIFIC DETAILS (keyholder name always current via users join)
// =========================================================
$boxDetails = [];
if (!empty($boxIds)) {
    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));
    $stmt = $pdo->prepare("
        SELECT bd.box_id,
               COALESCE(ku.username, bd.keyholder) AS keyholder,
               bd.keyholder_user_id,
               bd.box_content
        FROM box_details bd
        LEFT JOIN users ku ON ku.id = bd.keyholder_user_id
        WHERE bd.box_id IN ($placeholders)
    ");
    $stmt->execute($boxIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $details) {
        $boxDetails[(string) $details['box_id']] = $details;
    }
}

// =========================================================
// PRIMARY BOX PER MEMBER + LOCKED COUNT
// Prefer a currently locked box, otherwise the most recently registered one.
// =========================================================
$currentlyLockedCount = 0;
foreach ($members as $uid => &$m) {
    $m['primary_box'] = null;
    foreach ($m['boxes'] as $bid) {
        if (community_is_effectively_locked($latestStatus[$bid] ?? null)) {
            $m['primary_box'] = $bid;
            $currentlyLockedCount++;
            break;
        }
    }
    if ($m['primary_box'] === null && !empty($m['boxes'])) {
        $m['primary_box'] = $m['boxes'][0];
    }
}
unset($m);

// =========================================================
// OPEN A PROFILE VIA LINK: ?m=<user_id> or ?box=<box_id>
// =========================================================
$openUid = null;
if (isset($_GET['m']) && ctype_digit((string) $_GET['m']) && isset($members[(int) $_GET['m']])) {
    $openUid = (int) $_GET['m'];
} elseif (isset($_GET['box']) && preg_match('/^\d+$/D', (string) $_GET['box']) && isset($ownersByBox[(string) $_GET['box']])) {
    $openUid = (int) $ownersByBox[(string) $_GET['box']]['user_id'];
}

// =========================================================
// KEYHOLDER -> LOCKEES (who does a member currently hold locked?)
// Only public members are listed. Self-locks are skipped.
// =========================================================
$holdsByKeyholder = []; // keyholder user_id => list of lockees
foreach ($boxDetails as $bid => $d) {
    $khUid = !empty($d['keyholder_user_id'])
        ? (int) $d['keyholder_user_id']
        : ($memberByName[strtolower(trim((string) ($d['keyholder'] ?? '')))] ?? null);
    if (!$khUid || !isset($members[$khUid])) continue;

    $owner = $ownersByBox[$bid] ?? null;
    if (!$owner) continue;
    if ($khUid === (int) $owner['user_id']) continue;

    $st = $latestStatus[$bid] ?? null;
    if (!community_is_effectively_locked($st)) continue;

    $holdsByKeyholder[$khUid][] = [
        'uid'      => (int) $owner['user_id'],
        'name'     => (string) $owner['username'],
        'avatar'   => (string) ($owner['avatar_path'] ?? ''),
        'box'      => (string) $bid,
        'duration' => community_locked_duration($st['created_at'] ?? null),
    ];
}

// =========================================================
// DIALOG DATA (one entry per member, consumed by the JS below)
// =========================================================
$dialogData = [];
foreach ($members as $uid => $m) {
    $boxes = [];
    foreach ($m['boxes'] as $bid) {
        $st = $latestStatus[$bid] ?? null;
        $locked = community_is_effectively_locked($st);
        $d = $boxDetails[$bid] ?? [];

        $kh = $locked ? trim((string) ($d['keyholder'] ?? '')) : '';
        $khUid = null;
        if ($kh !== '') {
            $khUid = !empty($d['keyholder_user_id'])
                ? (int) $d['keyholder_user_id']
                : ($memberByName[strtolower($kh)] ?? null);
            if ($khUid !== null && !isset($members[$khUid])) $khUid = null; // not public: no link, no avatar
        }

        $boxes[] = [
            'id'               => $bid,
            'locked'           => $locked,
            'known'            => community_box_state($st) !== 'unknown',
            'duration'         => $locked ? community_locked_duration($st['created_at'] ?? null) : '',
            'content'          => (string) ($d['box_content'] ?? ''),
            'keyholder'        => $kh,
            'keyholder_uid'    => $khUid,
            'keyholder_avatar' => $khUid ? (string) ($members[$khUid]['avatar_path'] ?? '') : '',
        ];
    }

    $dialogData[$uid] = [
        'uid'      => $uid,
        'name'     => (string) $m['username'],
        'pro'      => (string) ($m['pro_version'] ?? '0') === '1',
        'avatar'   => (string) ($m['avatar_path'] ?? ''),
        'age'      => profileAge($m['birth_date'] ?? null),
        'headline' => trim((string) ($m['headline'] ?? '')),
        'facts'    => profileBuildFacts($m),
        'extra'    => profileRenderExtras($m, []),
        'boxes'    => $boxes,
        'holds'    => $holdsByKeyholder[$uid] ?? [],
        'socials'  => [
            'X'         => trim((string) ($m['social_x'] ?? '')),
            'Instagram' => trim((string) ($m['social_instagram'] ?? '')),
            'Bluesky'   => trim((string) ($m['social_bluesky'] ?? '')),
        ],
    ];
}

// =========================================================
// RECENT ACTIVITY (latest event per box, three different boxes)
// =========================================================
$recentActivityByBox = [];

foreach ($historyByBox as $boxId => $history) {
    $owner = $ownersByBox[$boxId] ?? null;
    if (!$owner) continue;
    $lockeeName = trim((string) ($owner['username'] ?? '')) ?: 'A Lockee';

    foreach ($history as $index => $event) {
        $state = community_box_state($event);
        if ($state === 'unknown') continue;

        $activity = [
            'box_id'        => $boxId,
            'uid'           => (int) $owner['user_id'],
            'name'          => $lockeeName,
            'avatar'        => $owner['avatar_path'] ?? '',
            'status'        => $state,
            'created_at'    => $event['created_at'],
            'relative_time' => community_relative_time($event['created_at']),
            'duration'      => null,
        ];

        if ($state === 'unlocked' && isset($history[$index + 1]) && community_box_state($history[$index + 1]) === 'locked') {
            $start = strtotime($history[$index + 1]['created_at']);
            $end   = strtotime($event['created_at']);
            if ($start !== false && $end !== false && $end > $start) {
                $activity['duration'] = community_format_duration($end - $start);
            }
        }

        $recentActivityByBox[$boxId] = $activity;
        break;
    }
}

$recentActivity = array_values($recentActivityByBox);
usort($recentActivity, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));
$recentActivity = array_slice($recentActivity, 0, 3);

?>
<link rel="stylesheet" href="/assets/css/community.css">
<link rel="stylesheet" href="/assets/css/profile_view.css">

<div class="settings-wrapper">
    <div class="settings-container">

        <!-- COMMUNITY HEADER -->
        <div class="community-hero">
            <h1>Community</h1>
            <p>
                <?= $publicUserCount ?> public profiles
                &middot;
                <?= $currentlyLockedCount ?> currently locked
            </p>
        </div>

        <!-- RECENT ACTIVITY -->
        <section class="community-section">
            <div class="community-section-header">
                <h2 class="community-section-title">Recent Activity</h2>
            </div>

            <?php if (empty($recentActivity)): ?>
                <div class="community-empty">No recent activity yet.</div>
            <?php else: ?>
                <div class="community-activity">
                    <?php foreach ($recentActivity as $activity): ?>
                        <button type="button"
                                class="community-activity-item js-open-member"
                                data-uid="<?= (int) $activity['uid'] ?>"
                                aria-label="View profile of <?= htmlspecialchars($activity['name'], ENT_QUOTES, 'UTF-8') ?>">

                            <?php if (!empty($activity['avatar'])): ?>
                                <img class="community-activity-avatar"
                                     src="<?= htmlspecialchars($activity['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <span class="community-activity-avatar-placeholder" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="8" r="4"></circle>
                                        <path d="M4 21v-2a8 8 0 0 1 16 0v2"></path>
                                    </svg>
                                </span>
                            <?php endif; ?>

                            <span class="community-activity-status <?= $activity['status'] === 'unlocked' ? 'open' : '' ?>" aria-hidden="true"></span>

                            <span class="community-activity-text">
                                <span class="community-activity-name"><?= htmlspecialchars($activity['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php if ($activity['status'] === 'locked'): ?>
                                    <?php if (!empty($activity['duration'])): ?>
                                        was locked for <span class="community-activity-duration"><?= htmlspecialchars($activity['duration'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php else: ?>
                                        was locked
                                    <?php endif; ?>
                                <?php else: ?>
                                    was unlocked
                                <?php endif; ?>
                            </span>

                            <span class="community-activity-time"><?= htmlspecialchars($activity['relative_time'], ENT_QUOTES, 'UTF-8') ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- MEMBERS -->
        <section class="community-section">
            <div class="community-section-header">
                <h2 class="community-section-title">Members</h2>
                <span class="community-section-meta" id="communityCount"><?= $publicUserCount ?> members</span>
            </div>

            <!-- FILTER -->
            <div class="community-filters" id="communityFilters">
                <div class="community-filter-group" role="group" aria-label="Filter by role">
                    <span class="community-filter-label">Role</span>
                    <button type="button" class="community-filter-btn is-active" data-filter-group="role" data-filter-value="all">All</button>
                    <button type="button" class="community-filter-btn" data-filter-group="role" data-filter-value="keyholder">Keyholders</button>
                    <button type="button" class="community-filter-btn" data-filter-group="role" data-filter-value="lockee">Lockees</button>
                </div>
                <div class="community-filter-group" role="group" aria-label="Filter by status">
                    <span class="community-filter-label">Status</span>
                    <button type="button" class="community-filter-btn is-active" data-filter-group="status" data-filter-value="all">All</button>
                    <button type="button" class="community-filter-btn" data-filter-group="status" data-filter-value="locked">Locked</button>
                    <button type="button" class="community-filter-btn" data-filter-group="status" data-filter-value="open">Open</button>
                </div>
            </div>

            <?php if (empty($members)): ?>
                <div class="community-empty">No public profiles yet.</div>
            <?php else: ?>
                <div class="community-lockee-grid" id="communityGrid">
                    <?php foreach ($members as $uid => $member): ?>
                        <?php
                            $id = (string) ($member['primary_box'] ?? '');
                            $hasBox = $id !== '';
                            $status = $hasBox ? ($latestStatus[$id] ?? null) : null;

                            $state = community_box_state($status);
                            $isLocked = $hasBox && community_is_effectively_locked($status);

                            if (!$hasBox || $state === 'unknown') {
                                $filterState = 'none';
                            } else {
                                $filterState = $isLocked ? 'locked' : 'open';
                            }

                            // One badge per currently locked box (with box content)
                            $lockedBadges = [];
                            foreach ($member['boxes'] as $bid) {
                                if (community_is_effectively_locked($latestStatus[$bid] ?? null)) {
                                    $lockedBadges[] = trim((string) ($boxDetails[$bid]['box_content'] ?? ''));
                                }
                            }

                            $lockeeName = trim((string) ($member['username'] ?? '')) ?: 'Member';
                            $avatarPath = (string) ($member['avatar_path'] ?? '');
                            $headline = trim((string) ($member['headline'] ?? ''));
                            $role = (string) ($member['profile_role'] ?? '');
                            $roleLabel = profileOptionLabel('profile_role', $role);
                            $age = profileAge($member['birth_date'] ?? null);
                            $isPro = (string) ($member['pro_version'] ?? '0') === '1';
                        ?>
                        <button
                            type="button"
                            class="community-tile js-open-member"
                            data-uid="<?= (int) $uid ?>"
                            data-role="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>"
                            data-state="<?= htmlspecialchars($filterState, ENT_QUOTES, 'UTF-8') ?>"
                            aria-haspopup="dialog"
                            aria-label="View profile for <?= htmlspecialchars($lockeeName, ENT_QUOTES, 'UTF-8') ?>"
                        >
                            <?php if ($avatarPath !== ''): ?>
                                <img class="community-tile-img" src="<?= htmlspecialchars($avatarPath, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <div class="community-tile-placeholder" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="8" r="4"></circle>
                                        <path d="M4 21v-2a8 8 0 0 1 16 0v2"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>

                            <?php if ($lockedBadges): ?>
                                <span class="community-tile-badges">
                                    <?php foreach ($lockedBadges as $content): ?>
                                        <span class="community-tile-badge" title="Locked<?= $content !== '' ? ': ' . htmlspecialchars($content, ENT_QUOTES, 'UTF-8') : '' ?>">
                                            <svg class="community-tile-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                                                <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                                            </svg>
                                            <?php if ($content !== ''): ?>
                                                <span class="community-tile-badge-text"><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php else: ?>
                                                <span class="visually-hidden">Locked</span>
                                            <?php endif; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($isPro): ?>
                                <span class="community-tile-pro" title="Pro member">PRO</span>
                            <?php endif; ?>

                            <span class="community-tile-info">
                                <span class="community-tile-name"><?= htmlspecialchars($lockeeName, ENT_QUOTES, 'UTF-8') ?><?php if ($age !== null): ?><span class="community-tile-age">, <?= (int) $age ?></span><?php endif; ?></span>
                                <?php if ($headline !== ''): ?>
                                    <span class="community-tile-headline"><?= htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                                <?php if ($roleLabel !== ''): ?>
                                    <span class="community-tile-role"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                <?php endif; ?>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="community-empty" id="communityNoMatch" hidden>No members match these filters.</div>
            <?php endif; ?>
        </section>

    </div>
</div>

<!-- MEMBER DIALOG (filled by JS) -->
<dialog id="memberDialog" class="member-dialog" aria-labelledby="mdName">
    <div class="md-head">
        <div id="mdAvatar" class="md-avatar"></div>
        <h2 id="mdName" class="md-name"></h2>
        <p id="mdHeadline" class="md-headline" hidden></p>
    </div>

    <div id="mdFacts" class="pv-chips md-chips" hidden></div>
    <div id="mdExtra"></div>

    <section id="mdHoldsSection" class="md-section" hidden>
        <h3 class="md-section-title">Keeps the key for</h3>
        <div id="mdHolds" class="md-list"></div>
    </section>

    <section id="mdBoxesSection" class="md-section" hidden>
        <h3 class="md-section-title" id="mdBoxesTitle">LockMeBox</h3>
        <div id="mdBoxes" class="md-list"></div>
    </section>

    <section id="mdSocialSection" class="md-section" hidden>
        <h3 class="md-section-title">Social</h3>
        <div id="mdSocials" class="md-socials"></div>
    </section>

    <div class="md-actions">
        <button type="button" class="btn-modern" id="mdBack" hidden>Back</button>
        <button type="button" class="btn-modern" id="mdClose">Close</button>
    </div>
</dialog>

<!-- HISTORY DIALOG -->
<dialog id="historyDialog" class="modern-dialog history-dialog">
    <h3>History – <span id="historyBoxName"></span></h3>
    <div id="historySummary"></div>
    <div id="historyContent" class="history-timeline"></div>
    <div class="dialog-actions">
        <button type="button" class="btn-modern" onclick="document.getElementById('historyDialog').close()">Close</button>
    </div>
</dialog>

<script>
const COMMUNITY_MEMBERS = <?= json_encode($dialogData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;

// ---------- Filter (role + status) ----------
(function () {
    const grid = document.getElementById('communityGrid');
    if (!grid) return;
    const cards = Array.from(grid.querySelectorAll('.community-tile'));
    const buttons = document.querySelectorAll('.community-filter-btn');
    const countEl = document.getElementById('communityCount');
    const noMatch = document.getElementById('communityNoMatch');
    const state = { role: 'all', status: 'all' };

    function roleMatches(role) {
        if (state.role === 'all') return true;
        if (state.role === 'keyholder') return role === 'keyholder' || role === 'switch';
        if (state.role === 'lockee')    return role === 'lockee' || role === 'switch';
        return true;
    }

    function apply() {
        let shown = 0;
        cards.forEach(function (card) {
            const ok = roleMatches(card.dataset.role || '') &&
                       (state.status === 'all' || card.dataset.state === state.status);
            card.hidden = !ok;
            if (ok) shown++;
        });
        const filtered = state.role !== 'all' || state.status !== 'all';
        countEl.textContent = filtered ? shown + ' of ' + cards.length + ' members' : cards.length + ' members';
        noMatch.hidden = shown > 0;
    }

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const group = btn.dataset.filterGroup;
            state[group] = btn.dataset.filterValue;
            buttons.forEach(function (b) {
                if (b.dataset.filterGroup === group) {
                    b.classList.toggle('is-active', b === btn);
                    b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
                }
            });
            apply();
        });
    });
})();

// ---------- Member dialog ----------
(function () {
    const dlg = document.getElementById('memberDialog');
    const stack = []; // uids visited via avatar clicks, for the Back button
    let current = null;

    const HISTORY_ICON = '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path></svg>';

    function el(tag, cls, text) {
        const n = document.createElement(tag);
        if (cls) n.className = cls;
        if (text !== undefined && text !== null) n.textContent = text;
        return n;
    }

    function avatar(src, size) {
        if (src) {
            const img = el('img', 'md-img md-img-' + size);
            img.src = src; img.alt = ''; img.loading = 'lazy';
            return img;
        }
        const ph = el('span', 'md-img md-img-' + size + ' md-img-ph');
        ph.textContent = '👤';
        return ph;
    }

    // Avatar (+ name) that opens another member's dialog if that member is public
    function personLink(uid, name, avatarSrc, size) {
        const known = uid && COMMUNITY_MEMBERS[uid] && uid !== current;
        const wrap = el(known ? 'button' : 'span', 'md-person' + (known ? ' is-link' : ''));
        if (known) {
            wrap.type = 'button';
            wrap.title = 'View profile of ' + name;
            wrap.addEventListener('click', function () { openMember(uid, true); });
        }
        wrap.appendChild(avatar(avatarSrc, size));
        wrap.appendChild(el('span', 'md-person-name', name));
        return wrap;
    }

    async function openHistory(boxId) {
        const box = document.getElementById('historyContent');
        document.getElementById('historyBoxName').textContent = 'LockMeBox ' + boxId;
        document.getElementById('historySummary').innerHTML = '';
        box.textContent = 'Loading …';
        document.getElementById('historyDialog').showModal();
        try {
            const res = await fetch(location.pathname + '?history_box=' + encodeURIComponent(boxId), { credentials: 'same-origin' });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Error');
            const entries = data.history || [];
            if (!entries.length) { box.innerHTML = '<p class="history-empty">No history available yet.</p>'; return; }
            if (typeof renderYearCalendar === 'function') {
                box.innerHTML = renderYearCalendar(entries);
            } else {
                box.textContent = '';
                entries.slice(0, 50).forEach(function (e) {
                    const row = el('div', 'md-row');
                    row.appendChild(el('span', '', e.created_at));
                    row.appendChild(el('span', 'md-row-meta', String(e.lock_status) === '1' ? 'Locked' : 'Open'));
                    box.appendChild(row);
                });
            }
        } catch (err) {
            box.textContent = 'Could not load history.';
        }
    }

    function render(m) {
        current = m.uid;

        const av = document.getElementById('mdAvatar');
        av.textContent = '';
        av.appendChild(avatar(m.avatar, 'xl'));

        const nameEl = document.getElementById('mdName');
        nameEl.textContent = m.name + (m.age ? ', ' + m.age : '');
        if (m.pro) {
            const pro = el('span', 'md-pro', 'PRO');
            pro.title = 'Pro member';
            nameEl.appendChild(pro);
        }

        const hl = document.getElementById('mdHeadline');
        hl.textContent = m.headline || '';
        hl.hidden = !m.headline;

        const facts = document.getElementById('mdFacts');
        facts.textContent = '';
        (m.facts || []).forEach(function (f) {
            const chip = el('span', 'pv-chip');
            chip.appendChild(el('small', '', f.label));
            chip.appendChild(document.createTextNode(f.value));
            facts.appendChild(chip);
        });
        facts.hidden = !(m.facts && m.facts.length);

        // Server-side escaped HTML (profileRenderExtras)
        document.getElementById('mdExtra').innerHTML = m.extra || '';

        // Keeps the key for
        const holdsList = document.getElementById('mdHolds');
        holdsList.textContent = '';
        (m.holds || []).forEach(function (h) {
            const row = el('div', 'md-row');
            row.appendChild(personLink(h.uid, h.name, h.avatar, 'md'));
            row.appendChild(el('span', 'md-row-meta', 'locked for ' + h.duration));
            holdsList.appendChild(row);
        });
        document.getElementById('mdHoldsSection').hidden = !(m.holds && m.holds.length);

        // Boxes
        const boxList = document.getElementById('mdBoxes');
        boxList.textContent = '';
        (m.boxes || []).forEach(function (b) {
            const card = el('div', 'md-box' + (b.locked ? ' is-locked' : ''));

            const top = el('div', 'md-box-top');
            top.appendChild(el('span', 'md-box-id', 'LockMeBox #' + b.id));
            const status = el('span', 'md-box-status ' + (b.locked ? 'is-locked' : (b.known ? 'is-open' : 'is-unknown')),
                b.locked ? 'Locked' + (b.duration ? ' · ' + b.duration : '') : (b.known ? 'Open' : 'Unknown'));
            top.appendChild(status);
            const hb = el('button', 'md-icon-btn');
            hb.type = 'button'; hb.title = 'Show History'; hb.setAttribute('aria-label', 'Show History');
            hb.innerHTML = HISTORY_ICON;   // static SVG, no user data
            hb.addEventListener('click', function () { openHistory(b.id); });
            top.appendChild(hb);
            card.appendChild(top);

            if (b.content) card.appendChild(el('div', 'md-box-content', '🔑 ' + b.content));

            if (b.locked && b.keyholder) {
                const kh = el('div', 'md-box-kh');
                kh.appendChild(el('span', 'md-row-meta', 'Locked by'));
                kh.appendChild(personLink(b.keyholder_uid, b.keyholder, b.keyholder_avatar, 'md'));
                card.appendChild(kh);
            }
            boxList.appendChild(card);
        });
        const n = (m.boxes || []).length;
        document.getElementById('mdBoxesTitle').textContent = n > 1 ? 'LockMeBoxes (' + n + ')' : 'LockMeBox';
        document.getElementById('mdBoxesSection').hidden = n === 0;

        // Socials (only https links)
        const soc = document.getElementById('mdSocials');
        soc.textContent = '';
        let any = false;
        Object.keys(m.socials || {}).forEach(function (label) {
            const url = m.socials[label];
            if (url && url.indexOf('https://') === 0) {
                const a = el('a', 'md-social', label);
                a.href = url; a.target = '_blank'; a.rel = 'noopener noreferrer';
                soc.appendChild(a);
                any = true;
            }
        });
        document.getElementById('mdSocialSection').hidden = !any;

        document.getElementById('mdBack').hidden = stack.length === 0;
        dlg.scrollTop = 0;

        // Address bar always holds a shareable link to the open profile
        history.replaceState(null, '', '?m=' + m.uid);
    }

    function openMember(uid, fromDialog) {
        const m = COMMUNITY_MEMBERS[uid];
        if (!m) return;
        if (fromDialog && current !== null) stack.push(current);
        else if (!fromDialog) stack.length = 0;
        render(m);
        if (!dlg.open) dlg.showModal();
    }

    // Tiles and recent-activity rows
    document.addEventListener('click', function (ev) {
        const trigger = ev.target.closest('.js-open-member');
        if (trigger) openMember(parseInt(trigger.dataset.uid, 10), false);
    });

    document.getElementById('mdClose').addEventListener('click', function () { dlg.close(); });
    document.getElementById('mdBack').addEventListener('click', function () {
        const prev = stack.pop();
        if (prev !== undefined) render(COMMUNITY_MEMBERS[prev]);
    });
    // click on the backdrop closes the dialog
    dlg.addEventListener('click', function (ev) { if (ev.target === dlg) dlg.close(); });
    dlg.addEventListener('close', function () {
        stack.length = 0;
        current = null;
        history.replaceState(null, '', location.pathname);
    });

    // Opened via link (?m=<user_id> or ?box=<box_id>)
    const OPEN_UID = <?= json_encode($openUid) ?>;
    if (OPEN_UID) openMember(OPEN_UID, false);
})();
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>