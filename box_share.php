<?php

session_start();

require_once __DIR__ . '/includes/config.php';

$bodyClass = "box-status-page";

$boxId = isset($_GET['box_id']) ? trim($_GET['box_id']) : '';

require_once __DIR__ . '/templates/header.php';


$box = null;

if (preg_match('/^\d+$/', $boxId)) {

    $stmt = $pdo->prepare("
        SELECT ub.box_id
        FROM user_boxes ub
        JOIN user_details ud ON ud.box_id = ub.box_id
        WHERE ub.box_id = :box_id
          AND ud.public_status = 1
    ");

    $stmt->execute([
        ':box_id' => $boxId
    ]);

    $box = $stmt->fetch(PDO::FETCH_ASSOC);
}


$status = null;
$details = null;


if ($box) {

    $stmt = $pdo->prepare("
        SELECT
            lock_status,
            created_at
        FROM box_data_history
        WHERE box_name = :box_id
        ORDER BY created_at DESC
        LIMIT 1
    ");

    $stmt->execute([
        ':box_id' => $boxId
    ]);

    $status = $stmt->fetch(PDO::FETCH_ASSOC);


    $stmt = $pdo->prepare("
        SELECT
            name_top,
            name_sub,
            box_content,
            avatar_path
        FROM user_details
        WHERE box_id = :box_id
    ");

    $stmt->execute([
        ':box_id' => $boxId
    ]);

    $details = $stmt->fetch(PDO::FETCH_ASSOC);
}


$isLocked = $status && (int) $status['lock_status'] === 1;


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

?>


<style>

/* =========================================================
   SINGLE PUBLIC BOX
   ========================================================= */

.box-status-page .settings-wrapper {
    min-height: 100vh;
    align-items: center;
}

.box-status-page .settings-container {
    max-width: 480px;
}


/* =========================================================
   PUBLIC BOX STATUS CARD
========================================================= */

.box-status-page .box-item {
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 32px 24px;
    gap: 8px;
}

.box-status-page .box-item-main {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 0;
    gap: 8px;
}

/* AVATAR */

.box-status-page .box-list-avatar,
.box-status-page .box-list-avatar-placeholder {
    width: 84px;
    height: 84px;
    min-width: 84px;
    min-height: 84px;
    border-radius: 50%;
    object-fit: cover;
}

/* LOCKEE NAME */

.box-status-page .box-title-row {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    width: 100%;
}

.box-status-page .box-title-content {
    display: flex;
    justify-content: center;
    width: 100%;
    min-width: 0;
}

.box-status-page .lockee-title {
    color: #f4f4f5;
    font-size: 22px;
    font-weight: 700;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

/* SECONDARY BOX ID */

.box-status-page .box-id {
    color: #71717a;
    font-size: 12px;
    font-weight: 400;
}

/* LOCK RELATION */

.box-status-page .box-lock-relation {
    width: 100%;
    display: flex;
    align-items: baseline;
    justify-content: center;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 2px;
    font-size: 13px;
    line-height: 1.5;
}

.box-status-page .lockee-name {
    display: none;
}

.box-status-page .relation-text {
    color: #71717a;
}

.box-status-page .keyholder-name {
    color: #d4d4dc;
    font-weight: 600;
    overflow-wrap: anywhere;
}

/* BOX CONTENT */

.box-status-page .box-content-tag {
    display: flex;
    justify-content: center;
    margin-top: 8px;
}

/* LOCK STATUS */

.box-status-page .box-lock-status {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 7px;
    margin-top: 4px;
    font-size: 14px;
}

/* REMOVE INNER CARD STYLING */

.box-status-page .settings-card .box-item {
    background: none;
    border: none;
    padding: 0;
}

/* MOBILE */

@media (max-width: 480px) {
    .box-status-page .settings-container {
        max-width: 100%;
    }

    .box-status-page .box-item {
        padding: 24px 16px;
    }

    .box-status-page .lockee-title {
        font-size: 20px;
    }

    .box-status-page .box-lock-relation {
        font-size: 13px;
    }
}


/* =========================================================
   LOCK RELATION
   ========================================================= */

.box-status-page .box-lock-relation {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: nowrap;
    white-space: nowrap;
    gap: 5px;
    margin-top: 4px;
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
   BOX CONTENT
   ========================================================= */

.box-status-page .box-content-tag {
    justify-content: center;
}


/* =========================================================
   LOCK STATUS
   ========================================================= */

.box-status-page .box-lock-status {
    justify-content: center;
    font-size: 14px;
    margin-top: 4px;
}


/* =========================================================
   REMOVE CARD STYLING FROM OUTER BOX
   ========================================================= */

.box-status-page .settings-card .box-item {
    background: none;
    border: none;
    padding: 0;
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 480px) {

    .box-status-page .settings-container {
        max-width: 100%;
    }

    .box-status-page .box-item {
        padding: 24px 16px;
    }

    .box-status-page .box-lock-relation {
        font-size: 14px;
    }
}

</style>


<div class="settings-wrapper">

    <div class="settings-container">

        <?php if (!$box): ?>

            <p class="box-empty">
                This box's status isn't shared publicly.
            </p>

        <?php else: ?>

            <div class="settings-card">

                <div class="settings-card-body">

                    <div class="box-item">

                        <div class="box-item-main">


                            <!-- =================================================
                                 BOX TITLE
                            ================================================== -->

                            <!-- BOX TITLE -->

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
                                    <span class="lockee-title">
                                        <?= htmlspecialchars(
                                            !empty($details['name_sub'])
                                                ? $details['name_sub']
                                                : 'Lockee'
                                        ) ?>
                                    </span>
                                </div>

                                <span class="box-id">
                                    LockMeBox #<?= htmlspecialchars($boxId) ?>
                                </span>

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
                                !empty($status['created_at'])
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
                                            locked_duration($status['created_at'])
                                        ) ?>
                                    </span>

                                </div>

                            <?php endif; ?>


                        </div>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>


<?php require_once __DIR__ . '/templates/footer.php'; ?>
