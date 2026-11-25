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

        $historyStmt = $pdo->prepare("SELECT lock_status, open_time, created_at, protection_level_timer FROM box_data_history WHERE box_id = :box_id ORDER BY created_at DESC LIMIT 100");
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
    SELECT u.id AS user_id, u.username, u.created_at AS member_since,
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
foreach ($members as $m) {
    foreach ($m['boxes'] as $bid) {
        $boxIds[] = $bid;
        $ownersByBox[$bid] = $m;
    }
}

// Lock statistics per user (for badges). Users without a box are included.
$userStats = profileStatsForPublicBoxes($pdo, $publicRows);

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
// BOX-SPECIFIC DETAILS
// =========================================================
$boxDetails = [];
if (!empty($boxIds)) {
    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));
    $stmt = $pdo->prepare("SELECT box_id, keyholder, box_content FROM box_details WHERE box_id IN ($placeholders)");
    $stmt->execute($boxIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $details) {
        $boxDetails[(string) $details['box_id']] = $details;
    }
}

// =========================================================
// KEYHOLDER AVATARS (only if the keyholder's own profile is public)
// =========================================================
$keyholderAvatars = [];
$keyholderNames = [];
foreach ($boxDetails as $details) {
    $kh = trim((string) ($details['keyholder'] ?? ''));
    if ($kh !== '') $keyholderNames[$kh] = true;
}
$keyholderNames = array_keys($keyholderNames);

if (!empty($keyholderNames)) {
    $placeholders = implode(',', array_fill(0, count($keyholderNames), '?'));
    $stmt = $pdo->prepare("
        SELECT u.username, ud.avatar_path
        FROM users u
        JOIN user_details ud ON ud.user_id = u.id
        WHERE ud.profile_visibility = 'public'
          AND u.username IN ($placeholders)
    ");
    $stmt->execute($keyholderNames);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (!empty($row['avatar_path'])) {
            $keyholderAvatars[strtolower($row['username'])] = $row['avatar_path'];
        }
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
                <span class="community-section-meta">Latest public activity</span>
            </div>

            <?php if (empty($recentActivity)): ?>
                <div class="community-empty">No recent activity yet.</div>
            <?php else: ?>
                <div class="community-activity">
                    <?php foreach ($recentActivity as $activity): ?>
                        <div class="community-activity-item">

                            <?php if (!empty($activity['avatar'])): ?>
                                <img class="community-activity-avatar"
                                     src="<?= htmlspecialchars($activity['avatar']) ?>" alt="" loading="lazy">
                            <?php else: ?>
                                <div class="community-activity-avatar-placeholder" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="8" r="4"></circle>
                                        <path d="M4 21v-2a8 8 0 0 1 16 0v2"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>

                            <span class="community-activity-status <?= $activity['status'] === 'unlocked' ? 'open' : '' ?>" aria-hidden="true"></span>

                            <div class="community-activity-content">
                                <div class="community-activity-text">
                                    <span class="community-activity-name"><?= htmlspecialchars($activity['name']) ?></span>
                                    <?php if ($activity['status'] === 'locked'): ?>
                                        <?php if (!empty($activity['duration'])): ?>
                                            was locked for
                                            <span class="community-activity-duration"><?= htmlspecialchars($activity['duration']) ?></span>
                                        <?php else: ?>
                                            was locked
                                        <?php endif; ?>
                                    <?php else: ?>
                                        was unlocked
                                    <?php endif; ?>
                                </div>
                                <span class="community-activity-time"><?= htmlspecialchars($activity['relative_time']) ?></span>
                            </div>
                        </div>
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
                    <?php foreach ($members as $member): ?>
                        <?php
                          $id = (string) ($member['primary_box'] ?? '');
                            $hasBox = $id !== '';
                            $status = $hasBox ? ($latestStatus[$id] ?? null) : null;
                            $details = $hasBox ? ($boxDetails[$id] ?? []) : [];

                            $state = community_box_state($status);
                            $isLocked = $hasBox && community_is_effectively_locked($status);

                            // data-state bleibt für den Filter erhalten
                            if (!$hasBox || $state === 'unknown') {
                                $filterState = 'none';
                            } else {
                                $filterState = $isLocked ? 'locked' : 'open';
                            }

                            // sichtbarer Status nur bei Locked
                            $statusLabel = $isLocked ? 'Locked' : '';
                            $statusClass = $isLocked ? 'is-locked' : '';

                            $lockeeName = trim((string) ($member['username'] ?? '')) ?: 'Member';
                            $avatarPath = (string) ($member['avatar_path'] ?? '');
                            $keyholderName = trim((string) ($details['keyholder'] ?? ''));
                            $keyholderAvatar = $keyholderName !== '' ? ($keyholderAvatars[strtolower($keyholderName)] ?? '') : '';
                            $boxContent = (string) ($details['box_content'] ?? '');
                            $socialX = trim((string) ($member['social_x'] ?? ''));
                            $socialInstagram = trim((string) ($member['social_instagram'] ?? ''));
                            $socialBluesky = trim((string) ($member['social_bluesky'] ?? ''));
                            $duration = ($isLocked && !empty($status['created_at'])) ? community_locked_duration($status['created_at']) : '';

                            $headline = trim((string) ($member['headline'] ?? ''));
                            $role = (string) ($member['profile_role'] ?? '');
                            $roleLabel = profileOptionLabel('profile_role', $role);

                            $age = profileAge($member['birth_date'] ?? null);
                            $facts = profileBuildFacts($member);
                            $extraHtml = profileRenderExtras($member, $userStats[(int) $member['user_id']] ?? profileEmptyStats());
                        ?>
                        <button
                            type="button"
                            class="community-lockee-card"
                            data-role="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>"
                            data-state="<?= htmlspecialchars($filterState, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-box="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-name="<?= htmlspecialchars($lockeeName, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-avatar="<?= htmlspecialchars($avatarPath, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-status="<?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-status-class="<?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-keyholder="<?= htmlspecialchars($keyholderName, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-keyholder-avatar="<?= htmlspecialchars($keyholderAvatar, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-content="<?= htmlspecialchars($boxContent, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-duration="<?= htmlspecialchars($duration, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-headline="<?= htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-facts="<?= htmlspecialchars(json_encode($facts, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-extra="<?= htmlspecialchars($extraHtml, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-social-x="<?= htmlspecialchars($socialX, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-social-instagram="<?= htmlspecialchars($socialInstagram, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-social-bluesky="<?= htmlspecialchars($socialBluesky, ENT_QUOTES, 'UTF-8') ?>"
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

                        <?php if ($isLocked): ?>
                            <span class="community-tile-badge">
                                <span class="community-tile-dot" aria-hidden="true"></span>
                                <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                            </span>
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

<dialog id="profileDialog" class="community-dialog" aria-labelledby="profileDialogName">
    <div class="community-profile-dialog-header">
        <div id="profileDialogAvatar"></div>
        <h2 id="profileDialogName" class="community-profile-dialog-name">Public profile</h2>
        <span id="profileDialogStatus" class="community-profile-status">
            <span class="community-status-dot" aria-hidden="true"></span>
            <span id="profileDialogStatusText"></span>
        </span>
    </div>

    <p id="profileDialogHeadline" class="community-profile-headline" hidden></p>
    <div id="profileDialogFacts" class="community-profile-chips" hidden></div>
    <div id="profileDialogExtra"></div>

    <div class="community-profile-dialog-details">
        <div class="community-profile-detail" id="profileKeyholderRow">
            <span class="community-profile-detail-label">Keyholder</span>
            <span class="community-profile-detail-value community-keyholder">
                <img id="profileDialogKeyholderAvatar" class="community-keyholder-avatar" alt="" hidden>
                <span id="profileDialogKeyholder"></span>
            </span>
        </div>
        <div class="community-profile-detail" id="profileContentRow">
            <span class="community-profile-detail-label">Box content</span>
            <span class="community-profile-detail-value" id="profileDialogContent"></span>
        </div>
        <div class="community-profile-detail" id="profileDurationRow">
            <span class="community-profile-detail-label">Locked for</span>
            <span class="community-profile-detail-value" id="profileDialogDuration"></span>
        </div>
        <div class="community-profile-detail community-social-row" id="profileSocialRow" hidden>
            <span class="community-profile-detail-label">Social profiles</span>
            <div class="community-social-links" id="profileDialogSocials"></div>
        </div>
        <div class="community-profile-detail">
            <span class="community-profile-detail-label">Box</span>
            <span class="community-profile-detail-value" id="profileDialogBoxId"></span>
        </div>
    </div>

    <div class="community-dialog-actions">
        <button type="button" class="btn-modern" id="closeProfileDialog">Close</button>
    </div>
</dialog>

<script>
// ---------- Filter (role + status) ----------
(function () {
    const grid = document.getElementById('communityGrid');
    if (!grid) return;
    const cards = Array.from(grid.querySelectorAll('.community-lockee-card'));
    const buttons = document.querySelectorAll('.community-filter-btn');
    const countEl = document.getElementById('communityCount');
    const noMatch = document.getElementById('communityNoMatch');
    const state = { role: 'all', status: 'all' };

    function roleMatches(role) {
        if (state.role === 'all') return true;
        // "Switch" members show up under both Keyholders and Lockees
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
        countEl.textContent = filtered
            ? shown + ' of ' + cards.length + ' members'
            : cards.length + ' members';
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

// ---------- Dialog extras (headline, facts, extras, keyholder avatar) ----------
document.addEventListener('click', function (ev) {
    const card = ev.target.closest('.community-lockee-card');
    if (!card) return;

    const headline = card.dataset.profileHeadline || '';
    const hEl = document.getElementById('profileDialogHeadline');
    hEl.textContent = headline;
    hEl.hidden = headline === '';

    const box = document.getElementById('profileDialogFacts');
    box.textContent = '';
    let facts = [];
    try { facts = JSON.parse(card.dataset.profileFacts || '[]'); } catch (e) {}
    facts.forEach(function (f) {
        const chip = document.createElement('span');
        chip.className = 'community-profile-chip';
        const small = document.createElement('small');
        small.textContent = f.label;
        chip.appendChild(small);
        chip.appendChild(document.createTextNode(f.value));
        box.appendChild(chip);
    });
    box.hidden = facts.length === 0;

    // HTML is built and escaped server-side (profileRenderExtras)
    document.getElementById('profileDialogExtra').innerHTML = card.dataset.profileExtra || '';

    const khImg = document.getElementById('profileDialogKeyholderAvatar');
    const khAvatar = card.dataset.profileKeyholderAvatar || '';
    if (khAvatar) {
        khImg.src = khAvatar;
        khImg.hidden = false;
    } else {
        khImg.removeAttribute('src');
        khImg.hidden = true;
    }

       // Status-Badge nur bei Locked zeigen
    document.getElementById('profileDialogStatus').hidden = !card.dataset.profileStatus;
 
});
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>