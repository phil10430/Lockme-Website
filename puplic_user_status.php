
<?php
session_start();

require_once __DIR__ . '/includes/config.php';

$bodyClass = "box-status-page";

require_once __DIR__ . '/templates/header.php';


// =========================================================
// REGISTERED BOXES (box-level public_status)
// =========================================================

$stmt = $pdo->prepare("
    SELECT ub.box_id, ub.registered_at
    FROM user_boxes ub
    JOIN user_details ud ON ud.box_id = ub.box_id
    WHERE ud.public_status = 1
    ORDER BY ub.registered_at DESC
");
$stmt->execute();
$registeredBoxes = $stmt->fetchAll(PDO::FETCH_ASSOC);


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
// LATEST STATUS PER BOX
// =========================================================

$latestStatus = [];

if (!empty($registeredBoxes)) {

    $boxIds = array_column($registeredBoxes, 'box_id');
    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));

    $stmt = $pdo->prepare("
        SELECT
            box_name,
            lock_status,
            open_time,
            created_at,
            protection_level_timer,
            protection_level_password
        FROM box_data_history
        WHERE box_name IN ($placeholders)
        ORDER BY created_at DESC
    ");

    $stmt->execute($boxIds);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $status) {
        if (!isset($latestStatus[$status['box_name']])) {
            $latestStatus[$status['box_name']] = $status;
        }
    }
}


// =========================================================
// BOX STATE
// =========================================================

function box_state(?array $status): string
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


// =========================================================
// EFFECTIVE LOCK STATE
// =========================================================

function is_effectively_locked(?array $status): bool
{
    if (box_state($status) !== 'locked') {
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


// =========================================================
// LOCKED DURATION
// =========================================================

function locked_duration(?string $lockedSince): string
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


// =========================================================
// KEEP ONLY CURRENTLY LOCKED BOXES
// =========================================================

$registeredBoxes = array_values(
    array_filter(
        $registeredBoxes,
        function ($box) use ($latestStatus) {
            return is_effectively_locked(
                $latestStatus[$box['box_id']] ?? null
            );
        }
    )
);


// =========================================================
// LOAD BOX DETAILS + AVATAR
// =========================================================

$boxDetails = [];

if (!empty($registeredBoxes)) {

    $boxIds = array_column($registeredBoxes, 'box_id');
    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));

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
?>

<style>

/* =========================================================
   PUBLIC LOCKEE GRID
   ========================================================= */

.box-status-page .box-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
}

/*
 * Three wider columns on desktop.
 * The minimum width prevents the relation text
 * from wrapping unnecessarily.
 */
@media (min-width: 1100px) {
    .box-status-page .box-list {
        grid-template-columns: repeat(3, minmax(320px, 1fr));
    }
}

@media (min-width: 769px) and (max-width: 1099px) {
    .box-status-page .box-list {
        grid-template-columns: repeat(2, minmax(320px, 1fr));
    }
}

@media (max-width: 768px) {
    .box-status-page .box-list {
        grid-template-columns: 1fr;
    }
}


/* =========================================================
   BOX CARD
   ========================================================= */

.box-status-page .box-item {
    flex-direction: column;
    align-items: stretch;
    padding: 10px 12px;
    gap: 8px;
}

.box-status-page .box-item-main {
    width: 100%;
}


/* =========================================================
   LOCK RELATION
   ========================================================= */

.box-status-page .box-lock-relation {
    width: 100%;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    white-space: nowrap;
    gap: 5px;
}

.box-status-page .lockee-name,
.box-status-page .relation-text,
.box-status-page .keyholder-name {
    white-space: nowrap;
}

.box-status-page .lockee-name,
.box-status-page .keyholder-name {
    overflow: hidden;
    text-overflow: ellipsis;
}


/* =========================================================
   TITLE
   ========================================================= */

.box-status-page .box-title-content {
    min-width: 0;
}

.box-status-page .box-id {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}


</style>


<div class="settings-wrapper">

    <div class="settings-container">

        <div class="settings-hero">
            <div>
                <h1>Lockees</h1>

                <p>
                    <?= $totalLockedCount ?> locked total
                    &middot;
                    <?= count($registeredBoxes) ?> public
                </p>
            </div>
        </div>


        <div class="settings-card">

            <div class="settings-card-body">

                <?php if (empty($registeredBoxes)): ?>

                    <p class="box-empty">
                        No locked boxes right now.
                    </p>

                <?php else: ?>

                    <div class="box-list">

                        <?php foreach ($registeredBoxes as $box):

                            $id = $box['box_id'];
                            $status = $latestStatus[$id] ?? null;
                            $details = $boxDetails[$id] ?? null;

                            $actual = [
                                'locked_since' => $status['created_at'] ?? null
                            ];

                            $isLocked = true;

                        ?>

                            <div
                                class="box-item"
                                id="box-<?= htmlspecialchars($id) ?>">

                                <div class="box-item-main">


                                    <!-- =================================================
                                         BOX TITLE
                                    ================================================== -->

                                    <div class="box-title-row">

                                        <?php if ($details && !empty($details['avatar_path'])): ?>

                                            <img
                                                class="box-list-avatar"
                                                src="<?= htmlspecialchars($details['avatar_path']) ?>"
                                                alt=""
                                                loading="lazy">

                                        <?php else: ?>

                                            <div
                                                class="box-list-avatar-placeholder"
                                                aria-hidden="true">
                                            </div>

                                        <?php endif; ?>

                                        <div class="box-title-content">

                                            <span class="box-id">
                                                LockMeBox <?= htmlspecialchars($id) ?>
                                            </span>

                                        </div>

                                    </div>


                                    <!-- =================================================
                                         LOCK RELATION
                                    ================================================== -->

                                    <?php if (
                                        $details &&
                                        (
                                            !empty($details['name_sub']) ||
                                            !empty($details['name_top'])
                                        )
                                    ): ?>

                                        <div class="box-lock-relation">

                                            <?php if (!empty($details['name_sub'])): ?>

                                                <span class="lockee-name">
                                                    <?= htmlspecialchars($details['name_sub']) ?>
                                                </span>

                                            <?php endif; ?>


                                            <?php if (
                                                !empty($details['name_sub']) &&
                                                !empty($details['name_top'])
                                            ): ?>

                                                <span class="relation-text">
                                                    is locked by
                                                </span>

                                            <?php endif; ?>


                                            <?php if (!empty($details['name_top'])): ?>

                                                <span class="keyholder-name">
                                                    <?= htmlspecialchars($details['name_top']) ?>
                                                </span>

                                            <?php endif; ?>

                                        </div>

                                    <?php endif; ?>


                                    <!-- =================================================
                                         BOX CONTENT
                                    ================================================== -->

                                    <?php if (
                                        $details &&
                                        !empty($details['box_content'])
                                    ): ?>

                                        <div class="box-content-tag">

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
                                         LOCK STATUS
                                    ================================================== -->

                                    <?php if (
                                        $isLocked &&
                                        !empty($actual['locked_since'])
                                    ): ?>

                                        <div class="box-lock-status">

                                            <svg
                                                class="box-lock-icon"
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

                                            <span class="box-lock-duration">
                                                Locked since
                                                <?= htmlspecialchars(
                                                    locked_duration(
                                                        $actual['locked_since']
                                                    )
                                                ) ?>
                                            </span>

                                        </div>

                                    <?php endif; ?>


                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>


<?php require_once __DIR__ . '/templates/footer.php'; ?>
