<?php

session_start();

require_once __DIR__ . '/includes/config.php';

$bodyClass = "community-page";

// Return history only for a box that is currently public.
if (isset($_GET['history_box'])) {
    header('Content-Type: application/json; charset=utf-8');
    $historyBoxId = trim((string) $_GET['history_box']);

    try {
        $publicCheck = $pdo->prepare("SELECT 1 FROM user_boxes ub JOIN user_details ud ON ud.box_id = ub.box_id WHERE ub.box_id = :box_id AND ud.public_status = 1 LIMIT 1");
        $publicCheck->execute(['box_id' => $historyBoxId]);

        if (!$publicCheck->fetchColumn()) {
            http_response_code(404);
            echo json_encode(['error' => 'Public profile not found.']);
            exit;
        }

        $historyStmt = $pdo->prepare("SELECT lock_status, open_time, created_at, protection_level_timer FROM box_data_history WHERE box_name = :box_id ORDER BY created_at DESC LIMIT 100");
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

    if ($status === null) {

        return 'unknown';

    }

    $value = strtolower((string) $status['lock_status']);

    if (in_array($value, ['1', 'locked', 'true', 'closed'], true)) {

        return 'locked';

    }

    if (in_array($value, ['0', 'unlocked', 'false', 'open'], true)) {

        return 'unlocked';

    }

    return 'unknown';

}



function community_is_effectively_locked(?array $status): bool

{

    if (community_box_state($status) !== 'locked') {

        return false;

    }

    if (

        !empty($status['protection_level_timer']) &&

        !empty($status['open_time'])

    ) {

        $openTime = strtotime($status['open_time']);

        if ($openTime !== false && $openTime <= time()) {

            return false;

        }

    }

    return true;

}



function community_locked_duration(?string $lockedSince): string

{

    if (!$lockedSince) {

        return '';

    }

    $timestamp = strtotime($lockedSince);

    if ($timestamp === false) {

        return '';

    }

    $diff = max(0, time() - $timestamp);

    $days = intdiv($diff, 86400);

    $hours = intdiv($diff % 86400, 3600);

    $minutes = intdiv($diff % 3600, 60);

    $parts = [];

    if ($days > 0) {

        $parts[] = $days . 'd';

    }

    if ($hours > 0) {

        $parts[] = $hours . 'h';

    }

    if ($minutes > 0 || empty($parts)) {

        $parts[] = $minutes . 'm';

    }

    return implode(' ', $parts);

}



function community_relative_time(?string $date): string

{

    if (!$date) {

        return '';

    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {

        return '';

    }

    $diff = max(0, time() - $timestamp);

    if ($diff < 60) {

        return 'just now';

    }

    if ($diff < 3600) {

        $minutes = intdiv($diff, 60);

        return $minutes . ($minutes === 1 ? ' minute ago' : ' minutes ago');

    }

    if ($diff < 86400) {

        $hours = intdiv($diff, 3600);

        return $hours . ($hours === 1 ? ' hour ago' : ' hours ago');

    }

    if ($diff < 604800) {

        $days = intdiv($diff, 86400);

        return $days . ($days === 1 ? ' day ago' : ' days ago');

    }

    return date('M j, Y', $timestamp);

}



// =========================================================

// PUBLIC BOXES

// =========================================================

$stmt = $pdo->prepare("

    SELECT

        ub.box_id,

        ub.registered_at

    FROM user_boxes ub

    JOIN user_details ud

        ON ud.box_id = ub.box_id

    WHERE ud.public_status = 1

    ORDER BY ub.registered_at DESC

");

$stmt->execute();

$publicBoxes = $stmt->fetchAll(PDO::FETCH_ASSOC);



// =========================================================

// BOX IDS

// =========================================================

$boxIds = array_column($publicBoxes, 'box_id');



// =========================================================

// TOTAL LOCKED COUNT

// =========================================================

$totalLockedCount = 0;

try {

    $stmt = $pdo->prepare("

        SELECT COUNT(*)

        FROM box_data_actual

        WHERE lock_status = 1

    ");

    $stmt->execute();

    $totalLockedCount = (int) $stmt->fetchColumn();

} catch (PDOException $e) {

    $totalLockedCount = 0;

}



// =========================================================

// LOAD BOX HISTORY

// =========================================================

$historyByBox = [];

if (!empty($boxIds)) {

    $placeholders = implode(

        ',',

        array_fill(0, count($boxIds), '?')

    );

    $stmt = $pdo->prepare("

        SELECT

            box_name,

            lock_status,

            open_time,

            created_at,

            protection_level_timer

        FROM box_data_history

        WHERE box_name IN ($placeholders)

        ORDER BY created_at DESC

    ");

    $stmt->execute($boxIds);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $history) {

        $boxName = $history['box_name'];

        if (!isset($historyByBox[$boxName])) {

            $historyByBox[$boxName] = [];

        }

        /*

         * Keep a limited amount of history per public box.

         * This is enough for the recent activity feed without

         * loading the complete history table.

         */

        if (count($historyByBox[$boxName]) < 10) {

            $historyByBox[$boxName][] = $history;

        }

    }

}



// =========================================================

// LATEST STATUS PER BOX

// =========================================================

$latestStatus = [];

foreach ($historyByBox as $boxId => $history) {

    if (!empty($history)) {

        $latestStatus[$boxId] = $history[0];

    }

}



// =========================================================

// LOAD BOX DETAILS

// =========================================================

$boxDetails = [];

if (!empty($boxIds)) {

    $placeholders = implode(

        ',',

        array_fill(0, count($boxIds), '?')

    );

    $stmt = $pdo->prepare("

        SELECT

            box_id,

            name_top,

            name_sub,

            box_content,

            avatar_path,

            public_status,

            social_x,

            social_instagram,

            social_bluesky

        FROM user_details

        WHERE box_id IN ($placeholders)

    ");

    $stmt->execute($boxIds);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $details) {

        $boxDetails[$details['box_id']] = $details;

    }

}



// =========================================================
// PUBLIC PROFILES AND LOCKED COUNT
// =========================================================

$publicProfiles = $publicBoxes;
$currentlyLockedCount = 0;

foreach ($publicProfiles as $profile) {
    $profileId = $profile['box_id'];
    if (community_is_effectively_locked($latestStatus[$profileId] ?? null)) {
        $currentlyLockedCount++;
    }
}


// BUILD RECENT ACTIVITY

// Keep only the latest activity from each box.

// This guarantees that the three displayed activities come from three different boxes.

$recentActivityByBox = [];

foreach ($historyByBox as $boxId => $history) {

    $details = $boxDetails[$boxId] ?? null;

    if (!$details) continue;

    $lockeeName = trim((string) ($details['name_sub'] ?? ''));

    if ($lockeeName === '') {

        $lockeeName = 'A Lockee';

    }

    foreach ($history as $index => $event) {

        $state = community_box_state($event);

        if ($state === 'unknown') {

            continue;

        }

        $activity = [

            'box_id' => $boxId,

            'name' => $lockeeName,

            'avatar' => $details['avatar_path'] ?? '',

            'status' => $state,

            'created_at' => $event['created_at'],

            'relative_time' => community_relative_time($event['created_at']),

            'duration' => null

        ];

        if ($state === 'locked' && isset($history[$index + 1])) {

            $olderEvent = $history[$index + 1];

            $start = strtotime($event['created_at']);

            $end = strtotime($olderEvent['created_at']);

            if ($start !== false && $end !== false && $end > $start) {

                $durationSeconds = $end - $start;

                $days = intdiv($durationSeconds, 86400);

                $hours = intdiv($durationSeconds % 86400, 3600);

                $minutes = intdiv($durationSeconds % 3600, 60);

                $parts = [];

                if ($days > 0) {

                    $parts[] = $days . 'd';

                }

                if ($hours > 0) {

                    $parts[] = $hours . 'h';

                }

                if ($minutes > 0 || empty($parts)) {

                    $parts[] = $minutes . 'm';

                }

                $activity['duration'] = implode(' ', $parts);

            }

        }

        // History is already ordered newest first.

        // The first valid event is therefore the latest activity for this box.

        $recentActivityByBox[$boxId] = $activity;

        break;

    }

}

// Sort the latest activity from each box by date.

$recentActivity = array_values($recentActivityByBox);

usort(

    $recentActivity,

    function ($a, $b) {

        return strtotime($b['created_at']) <=> strtotime($a['created_at']);

    }

);

// Show only three activities from three different boxes.

$recentActivity = array_slice($recentActivity, 0, 3);





?>

<link rel="stylesheet" href="/assets/css/lockees.css">



<div class="settings-wrapper">

    <div class="settings-container">



        <!-- =====================================================

             COMMUNITY HEADER

        \====================================================== -->

        <div class="community-hero">

            <h1>Lockees</h1>

            <p>

                <?= count($publicProfiles) ?> public profiles

                &middot;

                <?= $currentlyLockedCount ?> currently locked

            </p>

        </div>



        <!-- =====================================================

             RECENT ACTIVITY

        \====================================================== -->

        <section class="community-section">

            <div class="community-section-header">

                <h2 class="community-section-title">

                    Recent Activity

                </h2>

                <span class="community-section-meta">

                    Latest public activity

                </span>

            </div>



            <?php if (empty($recentActivity)): ?>

                <div class="community-empty">

                    No recent activity yet.

                </div>

            <?php else: ?>

                <div class="community-activity">

                    <?php foreach ($recentActivity as $activity): ?>

                        <div class="community-activity-item">

                            
                        <?php if (!empty($activity['avatar'])): ?>
                            <img class="community-activity-avatar"
                                src="<?= htmlspecialchars($activity['avatar']) ?>"
                                alt=""
                                loading="lazy">
                        <?php else: ?>
                            <div class="community-activity-avatar-placeholder" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="8" r="4"></circle>
                                    <path d="M4 21v-2a8 8 0 0 1 16 0v2"></path>
                                </svg>
                            </div>
                        <?php endif; ?>



                            <span

                                class="community-activity-status <?= $activity['status'] === 'unlocked' ? 'open' : '' ?>"

                                aria-hidden="true">

                            </span>



                            <div class="community-activity-content">

                                <div class="community-activity-text">

                                    <span class="community-activity-name">

                                        <?= htmlspecialchars($activity['name']) ?>

                                    </span>

                                    <?php if ($activity['status'] === 'locked'): ?>

                                        <?php if (!empty($activity['duration'])): ?>

                                            was locked for

                                            <span class="community-activity-duration">

                                                <?= htmlspecialchars($activity['duration']) ?>

                                            </span>

                                        <?php else: ?>

                                            was locked

                                        <?php endif; ?>

                                    <?php else: ?>

                                        was unlocked

                                    <?php endif; ?>

                                </div>



                                <span class="community-activity-time">

                                    <?= htmlspecialchars($activity['relative_time']) ?>

                                </span>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>



        <!-- Public profile directory -->
        <section class="community-section">
            <div class="community-section-header">
                <h2 class="community-section-title">Public Profiles</h2>
                <span class="community-section-meta"><?= count($publicProfiles) ?> profiles</span>
            </div>

            <?php if (empty($publicProfiles)): ?>
                <div class="community-empty">No public profiles yet.</div>
            <?php else: ?>
                <div class="community-lockee-grid">
                    <?php foreach ($publicProfiles as $box): ?>
                        <?php
                            $id = (string) $box['box_id'];
                            $status = $latestStatus[$id] ?? null;
                            $details = $boxDetails[$id] ?? null;
                            if (!$details) continue;

                            $state = community_box_state($status);
                            $isLocked = community_is_effectively_locked($status);
                            $statusLabel = $state === 'unknown' ? 'Unknown' : ($isLocked ? 'Locked' : 'Open');
                            $statusClass = $statusLabel === 'Locked' ? 'is-locked' : ($statusLabel === 'Open' ? 'is-open' : 'is-unknown');
                            $lockeeName = trim((string) ($details['name_sub'] ?? '')) ?: 'Lockee';
                            $avatarPath = (string) ($details['avatar_path'] ?? '');
                            $keyholderName = (string) ($details['name_top'] ?? '');
                            $boxContent = (string) ($details['box_content'] ?? '');
                            $socialX = trim((string) ($details['social_x'] ?? ''));
                            $socialInstagram = trim((string) ($details['social_instagram'] ?? ''));
                            $socialBluesky = trim((string) ($details['social_bluesky'] ?? ''));
                            $duration = ($isLocked && !empty($status['created_at'])) ? community_locked_duration($status['created_at']) : '';
                        ?>
                        <button
                            type="button"
                            class="community-lockee-card"
                            data-profile-box="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-name="<?= htmlspecialchars($lockeeName, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-avatar="<?= htmlspecialchars($avatarPath, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-status="<?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-status-class="<?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-keyholder="<?= htmlspecialchars($keyholderName, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-content="<?= htmlspecialchars($boxContent, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-duration="<?= htmlspecialchars($duration, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-social-x="<?= htmlspecialchars($socialX, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-social-instagram="<?= htmlspecialchars($socialInstagram, ENT_QUOTES, 'UTF-8') ?>"
                            data-profile-social-bluesky="<?= htmlspecialchars($socialBluesky, ENT_QUOTES, 'UTF-8') ?>"
                            aria-haspopup="dialog"
                            aria-label="View profile for <?= htmlspecialchars($lockeeName, ENT_QUOTES, 'UTF-8') ?>"
                        >
                            
                        <?php if ($avatarPath !== ''): ?>
                            <img class="community-lockee-avatar" src="<?= htmlspecialchars($avatarPath, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <div class="community-lockee-avatar-placeholder" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="8" r="4"></circle>
                                    <path d="M4 21v-2a8 8 0 0 1 16 0v2"></path>
                                </svg>
                            </div>
                        <?php endif; ?>
                            <span class="community-lockee-name"><?= htmlspecialchars($lockeeName, ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="community-profile-status <?= $statusClass ?>">
                                <span class="community-status-dot" aria-hidden="true"></span>
                                <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span class="community-profile-hint">View profile</span>
                        </button>
                    <?php endforeach; ?>
                </div>
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

    <div class="community-profile-dialog-details">
        <div class="community-profile-detail" id="profileKeyholderRow">
            <span class="community-profile-detail-label">Keyholder</span>
            <span class="community-profile-detail-value" id="profileDialogKeyholder"></span>
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
 

<?php require_once __DIR__ . '/templates/footer.php'; ?>
