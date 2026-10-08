<?php

session_start();

require_once __DIR__ . '/includes/config.php';

$bodyClass = "community-page";

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
            public_status
        FROM user_details
        WHERE box_id IN ($placeholders)
    ");

    $stmt->execute($boxIds);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $details) {
        $boxDetails[$details['box_id']] = $details;
    }
}


// =========================================================
// KEEP ONLY CURRENTLY LOCKED PUBLIC BOXES
// =========================================================

$registeredBoxes = array_values(
    array_filter(
        $publicBoxes,
        function ($box) use ($latestStatus) {

            return community_is_effectively_locked(
                $latestStatus[$box['box_id']] ?? null
            );
        }
    )
);


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

<style>

/* =========================================================
   COMMUNITY PAGE
   All styles are intentionally scoped to this page.
   ========================================================= */

.community-page {
    --community-card: #222225;
    --community-card-hover: #28282c;
    --community-border: #37373c;
    --community-muted: #8f8f99;
    --community-text: #f3f4f6;
    --community-soft: #c9c9d0;
    --community-accent: #a78bfa;
}


/* =========================================================
   PAGE CONTAINER
   ========================================================= */

.community-page .settings-wrapper {
    align-items: flex-start;
}

.community-page .settings-container {
    max-width: 980px;
}


/* =========================================================
   COMMUNITY HERO
   ========================================================= */

.community-hero {
    margin-bottom: 18px;
}

.community-hero h1 {
    margin: 0;
    color: var(--community-text);
    font-size: 26px;
    font-weight: 700;
    letter-spacing: -0.5px;
}

.community-hero p {
    margin: 6px 0 0;
    color: var(--community-muted);
    font-size: 13px;
}


/* =========================================================
   COMMUNITY SECTION
   ========================================================= */

.community-section {
    margin-bottom: 18px;
}

.community-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.community-section-title {
    margin: 0;
    color: var(--community-text);
    font-size: 15px;
    font-weight: 650;
}

.community-section-meta {
    color: var(--community-muted);
    font-size: 12px;
}


/* =========================================================
   RECENT ACTIVITY
   ========================================================= */

.community-activity {
    display: flex;
    flex-direction: column;
    gap: 1px;
    overflow: hidden;
    background: var(--community-card);
    border: 1px solid var(--community-border);
    border-radius: 14px;
}

.community-activity-item {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
    padding: 12px 14px;
    background: transparent;
}

.community-activity-item + .community-activity-item {
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.community-activity-avatar,
.community-activity-avatar-placeholder {
    width: 34px;
    height: 34px;
    min-width: 34px;
    min-height: 34px;
    flex: 0 0 34px;
    border-radius: 50%;
    object-fit: cover;
}

.community-activity-avatar {
    border: 1px solid rgba(255, 255, 255, 0.12);
}

.community-activity-avatar-placeholder {
    background: #34343a;
    border: 1px solid #414147;
}

.community-activity-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
    flex: 1;
    gap: 2px;
}

.community-activity-text {
    color: var(--community-soft);
    font-size: 13px;
    line-height: 1.4;
}

.community-activity-name {
    color: #ffffff;
    font-weight: 650;
}

.community-activity-duration {
    color: #ffffff;
    font-weight: 600;
}

.community-activity-time {
    color: var(--community-muted);
    font-size: 11px;
}

.community-activity-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 7px;
    height: 7px;
    min-width: 7px;
    border-radius: 50%;
    background: #f87171;
}

.community-activity-status.open {
    background: #71717a;
}


/* =========================================================
   LOCKEE GRID
   ========================================================= */

.community-lockee-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}


/* =========================================================
   LOCKEE CARD
   ========================================================= */

.community-lockee-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 0;
    padding: 24px 18px 18px;
    background: var(--community-card);
    border: 1px solid var(--community-border);
    border-radius: 14px;
    text-align: center;
    box-sizing: border-box;
    transition:
        background 0.18s ease,
        border-color 0.18s ease,
        transform 0.18s ease;
}

.community-lockee-card:hover {
    background: var(--community-card-hover);
    border-color: #48484f;
    transform: translateY(-1px);
}


/* =========================================================
   LOCKEE AVATAR
   ========================================================= */

.community-lockee-avatar,
.community-lockee-avatar-placeholder {
    width: 82px;
    height: 82px;
    min-width: 82px;
    min-height: 82px;
    flex: 0 0 82px;
    border-radius: 50%;
    object-fit: cover;
}

.community-lockee-avatar {
    border: 2px solid rgba(255, 255, 255, 0.13);
}

.community-lockee-avatar-placeholder {
    background: #34343a;
    border: 2px solid #414147;
}


/* =========================================================
   LOCKEE NAME
   ========================================================= */

.community-lockee-name {
    max-width: 100%;
    margin-top: 13px;
    overflow: hidden;
    color: #ffffff;
    font-size: 17px;
    font-weight: 700;
    line-height: 1.3;
    text-overflow: ellipsis;
    white-space: nowrap;
}


/* =========================================================
   RELATION
   ========================================================= */
.community-lockee-relation {
    display: flex;
    align-items: baseline;
    justify-content: center;
    flex-wrap: wrap;
    max-width: 100%;
    margin-top: 4px;
    gap: 5px;
    color: #71717a;
    font-size: 12px;
    line-height: 1.4;
}

.community-lockee-keyholder {
    max-width: none;
    overflow: visible;
    color: #d4d4dc;
    font-weight: 600;
    text-overflow: clip;
    white-space: normal;
    overflow-wrap: anywhere;
}


/* =========================================================
   KEY CONTENT
   ========================================================= */

.community-lockee-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    max-width: 100%;
    margin-top: 12px;
    color: #a1a1aa;
    font-size: 12px;
}

.community-lockee-content svg {
    width: 14px;
    height: 14px;
    flex: 0 0 14px;
    color: #85858f;
}

.community-lockee-content span {
    overflow: hidden;
    color: #b8b8c1;
    text-overflow: ellipsis;
    white-space: nowrap;
}


/* =========================================================
   LOCKED STATUS
   ========================================================= */

.community-lockee-status {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 11px;
    color: #a1a1aa;
    font-size: 12px;
}

.community-lockee-status svg {
    width: 14px;
    height: 14px;
    flex: 0 0 14px;
    color: #fca5a5;
}


/* =========================================================
   BOX ID
   ========================================================= */

.community-lockee-id {
    margin-top: 13px;
    color: #62626b;
    font-size: 10px;
    letter-spacing: 0.2px;
}


/* =========================================================
   EMPTY STATE
   ========================================================= */

.community-empty {
    padding: 30px 20px;
    background: var(--community-card);
    border: 1px solid var(--community-border);
    border-radius: 14px;
    color: var(--community-muted);
    font-size: 13px;
    text-align: center;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 850px) {

    .community-lockee-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}


@media (max-width: 600px) {

    .community-page .settings-container {
        width: 100%;
    }

    .community-lockee-grid {
        grid-template-columns: 1fr;
    }

    .community-lockee-card {
        padding: 22px 16px 17px;
    }

    .community-lockee-avatar,
    .community-lockee-avatar-placeholder {
        width: 74px;
        height: 74px;
        min-width: 74px;
        min-height: 74px;
        flex-basis: 74px;
    }

    .community-activity-item {
        padding: 11px 12px;
    }
}

</style>


<div class="settings-wrapper">

    <div class="settings-container">


        <!-- =====================================================
             COMMUNITY HEADER
        ====================================================== -->

        <div class="community-hero">

            <h1>Lockees</h1>

            <p>
                <?= count($registeredBoxes) ?> public lockees
                &middot;
                <?= $totalLockedCount ?> locked total
            </p>

        </div>


        <!-- =====================================================
             RECENT ACTIVITY
        ====================================================== -->

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

                                <img
                                    class="community-activity-avatar"
                                    src="<?= htmlspecialchars($activity['avatar']) ?>"
                                    alt=""
                                    loading="lazy">

                            <?php else: ?>

                                <div
                                    class="community-activity-avatar-placeholder"
                                    aria-hidden="true">
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


        <!-- =====================================================
             CURRENTLY LOCKED
        ====================================================== -->

        <section class="community-section">

            <div class="community-section-header">

                <h2 class="community-section-title">
                    Currently Locked
                </h2>

                <span class="community-section-meta">
                    <?= count($registeredBoxes) ?> public
                </span>

            </div>


            <?php if (empty($registeredBoxes)): ?>

                <div class="community-empty">
                    No public lockees are currently locked.
                </div>

            <?php else: ?>

                <div class="community-lockee-grid">

                    <?php foreach ($registeredBoxes as $box):

                        $id = $box['box_id'];
                        $status = $latestStatus[$id] ?? null;
                        $details = $boxDetails[$id] ?? null;

                        if (!$details) {
                            continue;
                        }

                    ?>

                        <article
                            class="community-lockee-card"
                            id="lockee-<?= htmlspecialchars($id) ?>">


                            <!-- =================================================
                                 AVATAR
                            ================================================== -->

                            <?php if (!empty($details['avatar_path'])): ?>

                                <img
                                    class="community-lockee-avatar"
                                    src="<?= htmlspecialchars($details['avatar_path']) ?>"
                                    alt=""
                                    loading="lazy">

                            <?php else: ?>

                                <div
                                    class="community-lockee-avatar-placeholder"
                                    aria-hidden="true">
                                </div>

                            <?php endif; ?>


                            <!-- =================================================
                                 LOCKEE
                            ================================================== -->

                            <?php if (!empty($details['name_sub'])): ?>

                                <div class="community-lockee-name">
                                    <?= htmlspecialchars($details['name_sub']) ?>
                                </div>

                            <?php else: ?>

                                <div class="community-lockee-name">
                                    Lockee
                                </div>

                            <?php endif; ?>


                            <!-- =================================================
                                 KEYHOLDER
                            ================================================== -->

                            <?php if (!empty($details['name_top'])): ?>

                                <div class="community-lockee-relation">

                                    <span>
                                        locked by
                                    </span>

                                    <span class="community-lockee-keyholder">
                                        <?= htmlspecialchars($details['name_top']) ?>
                                    </span>

                                </div>

                            <?php endif; ?>


                            <!-- =================================================
                                 BOX CONTENT
                            ================================================== -->

                            <?php if (!empty($details['box_content'])): ?>

                                <div class="community-lockee-content">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true">

                                        <circle
                                            cx="7.5"
                                            cy="15.5"
                                            r="5.5">
                                        </circle>

                                        <path d="m21 2-9.6 9.6"></path>
                                        <path d="m15.5 7.5 3 3"></path>
                                        <path d="m18.5 4.5 3 3"></path>

                                    </svg>

                                    <span>
                                        <?= htmlspecialchars($details['box_content']) ?>
                                    </span>

                                </div>

                            <?php endif; ?>


                            <!-- =================================================
                                 LOCKED SINCE
                            ================================================== -->

                            <?php if (
                                $status &&
                                !empty($status['created_at'])
                            ): ?>

                                <div class="community-lockee-status">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true">

                                        <rect
                                            x="5"
                                            y="11"
                                            width="14"
                                            height="10"
                                            rx="2">
                                        </rect>

                                        <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>

                                    </svg>

                                    <span>
                                        Locked for
                                        <?= htmlspecialchars(
                                            community_locked_duration(
                                                $status['created_at']
                                            )
                                        ) ?>
                                    </span>

                                </div>

                            <?php endif; ?>


                            <!-- =================================================
                                 SECONDARY BOX INFORMATION
                            ================================================== -->

                            <div class="community-lockee-id">
                                LockMeBox #<?= htmlspecialchars($id) ?>
                            </div>


                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </section>


    </div>

</div>


<?php require_once __DIR__ . '/templates/footer.php'; ?>
