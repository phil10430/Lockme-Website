<?php

session_start();

require_once __DIR__ . '/includes/config.php';

$bodyClass = "box-status-page";

$boxId = isset($_GET['box_id'])
    ? trim($_GET['box_id'])
    : '';

require_once __DIR__ . '/templates/header.php';

$box = null;

if (preg_match('/^\d+$/', $boxId)) {

    $stmt = $pdo->prepare("
        SELECT ub.box_id
        FROM user_boxes ub
        JOIN users u ON u.id = ub.user_id
        WHERE ub.box_id = :box_id
        AND u.public_status = 1
    ");

    $stmt->execute([
        ':box_id' => $boxId
    ]);

    $box = $stmt->fetch(PDO::FETCH_ASSOC);
}

$status = null;
$details = null;

if ($box) {

    // =====================================================
    // STATUS
    // =====================================================

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


    // =====================================================
    // BOX DETAILS + AVATAR
    // =====================================================

    $stmt = $pdo->prepare("
        SELECT
            name_top,
            name_sub,
            box_content,
            target_open_date,
            avatar_path
        FROM user_details
        WHERE box_id = :box_id
    ");

    $stmt->execute([
        ':box_id' => $boxId
    ]);

    $details = $stmt->fetch(PDO::FETCH_ASSOC);
}


$isLocked =
    $status &&
    (int) $status['lock_status'] === 1;


function locked_duration(
    ?string $lockedSince
): string {

    if (!$lockedSince) {
        return '';
    }

    $diff = max(
        0,
        time() - strtotime($lockedSince)
    );

    $days = intdiv(
        $diff,
        86400
    );

    $hours = intdiv(
        $diff % 86400,
        3600
    );

    $minutes = intdiv(
        $diff % 3600,
        60
    );

    $parts = [];

    if ($days > 0) {
        $parts[] = $days . 'd';
    }

    if ($hours > 0) {
        $parts[] = $hours . 'h';
    }

    if (
        $minutes > 0 ||
        empty($parts)
    ) {
        $parts[] = $minutes . 'm';
    }

    return implode(
        ' ',
        $parts
    );
}

?>

<style>

.box-status-page {
    --bg: #000000;
    --surface: #1c1f24;
    --border: #2b2f36;
    --text: #e9e7e2;
    --text-muted: #888d96;
    --locked: #f53030;
    --unlocked: #14eb26;

    background: var(--bg);
    color: var(--text);

    min-height: 100vh;
}


.share-wrapper {
    max-width: 420px;

    margin: 0 auto;

    padding:
        96px
        24px
        64px;
}


.share-card {
    background: var(--surface);

    border:
        1px solid
        var(--border);

    border-radius: 8px;

    padding: 24px;
}


/* =====================================================
   BOX HEADER
   ===================================================== */

.share-box-header {
    display: flex;

    align-items: center;

    gap: 10px;

    margin-bottom: 12px;

    min-width: 0;
}


.share-avatar {
    width: 40px;
    height: 40px;

    border-radius: 50%;

    object-fit: cover;

    flex-shrink: 0;

    border:
        1px solid
        rgba(255, 255, 255, 0.12);

    background:
        rgba(255, 255, 255, 0.06);
}


.share-avatar-placeholder {
    width: 40px;
    height: 40px;

    border-radius: 50%;

    flex-shrink: 0;

    background:
        rgba(255, 255, 255, 0.06);

    border:
        1px solid
        rgba(255, 255, 255, 0.12);
}


.share-box-id {
    font-family:
        ui-monospace,
        SFMono-Regular,
        Menlo,
        Consolas,
        monospace;

    font-size: 14px;

    color: var(--text-muted);

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


/* =====================================================
   STATE
   ===================================================== */

.share-state {
    display: inline-flex;

    align-items: center;

    gap: 8px;

    font-size: 16px;

    font-weight: 600;

    margin-bottom: 16px;
}


.share-state svg {
    width: 16px;
    height: 16px;
}


.share-state.locked {
    color: var(--locked);
}


.share-state.unlocked {
    color: var(--unlocked);
}


/* =====================================================
   DETAILS
   ===================================================== */

.share-details {
    display: flex;

    flex-direction: column;

    gap: 4px;

    font-size: 13px;

    color: var(--text-muted);
}


.share-details span {
    overflow: hidden;

    text-overflow: ellipsis;
}


.share-empty {
    text-align: center;

    color: var(--text-muted);

    font-size: 14px;

    padding: 40px 0;
}

</style>


<div class="share-wrapper">

    <?php if (!$box): ?>

        <p class="share-empty">
            This box's status isn't shared publicly.
        </p>

    <?php else: ?>

        <div class="share-card">

            <!-- =========================================
                 BOX HEADER + AVATAR
                 ========================================= -->

            <div class="share-box-header">

                <?php if (
                    $details &&
                    !empty($details['avatar_path'])
                ): ?>

                    <img
                        class="share-avatar"
                        src="<?= htmlspecialchars(
                            $details['avatar_path']
                        ) ?>"
                        alt=""
                    >

                <?php else: ?>

                    <div
                        class="share-avatar-placeholder"
                        aria-hidden="true"
                    ></div>

                <?php endif; ?>


                <div class="share-box-id">
                    LockMeBox
                    <?= htmlspecialchars($boxId) ?>
                </div>

            </div>


            <!-- =========================================
                 STATUS
                 ========================================= -->

            <div class="share-state <?= $isLocked ? 'locked' : 'unlocked' ?>">

                <?php if ($isLocked): ?>

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <rect
                            x="5"
                            y="11"
                            width="14"
                            height="10"
                            rx="2"
                        ></rect>

                        <path
                            d="M8 11V7a4 4 0 0 1 8 0v4"
                        ></path>
                    </svg>

                    Locked since
                    <?= htmlspecialchars(
                        locked_duration(
                            $status['created_at'] ?? null
                        )
                    ) ?>

                <?php else: ?>

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <rect
                            x="5"
                            y="11"
                            width="14"
                            height="10"
                            rx="2"
                        ></rect>

                        <path
                            d="M8 11V7a4 4 0 0 1 7.75-3.5"
                        ></path>
                    </svg>

                    Unlocked

                <?php endif; ?>

            </div>


            <!-- =========================================
                 DETAILS
                 ========================================= -->

            <?php if ($details): ?>

                <div class="share-details">

                    <?php if (
                        !empty($details['name_top'])
                    ): ?>

                        <span>
                            Keyholder:
                            <?= htmlspecialchars(
                                $details['name_top']
                            ) ?>
                        </span>

                    <?php endif; ?>


                    <?php if (
                        !empty($details['name_sub'])
                    ): ?>

                        <span>
                            Lockee:
                            <?= htmlspecialchars(
                                $details['name_sub']
                            ) ?>
                        </span>

                    <?php endif; ?>


                    <?php if (
                        !empty(
                            $details['target_open_date']
                        )
                    ): ?>

                        <span>
                            Target Open Date:
                            <?= htmlspecialchars(
                                date(
                                    'd.m.Y H:i',
                                    strtotime(
                                        $details[
                                            'target_open_date'
                                        ]
                                    )
                                )
                            ) ?>
                        </span>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>


<?php

require_once __DIR__ . '/templates/footer.php';

?>