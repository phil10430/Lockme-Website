<?php
session_start();

require_once __DIR__ . '/includes/config.php';

$bodyClass = "box-status-page";

require_once __DIR__ . '/templates/header.php';


// =========================================================
// REGISTERED BOXES
// =========================================================

$stmt = $pdo->prepare("
    SELECT ub.box_id, ub.registered_at
    FROM user_boxes ub
    JOIN users u ON u.id = ub.user_id
    WHERE u.public_status = 1
    ORDER BY ub.registered_at DESC
");

$stmt->execute();

$registeredBoxes = $stmt->fetchAll(PDO::FETCH_ASSOC);


// =========================================================
// LATEST STATUS PER BOX
// =========================================================

$latestStatus = [];

if (!empty($registeredBoxes)) {

    $boxIds = array_column(
        $registeredBoxes,
        'box_id'
    );

    $placeholders = implode(
        ',',
        array_fill(
            0,
            count($boxIds),
            '?'
        )
    );

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

    $historyRows = $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


    foreach ($historyRows as $r) {

        // First row per box is the newest
        if (!isset(
            $latestStatus[$r['box_name']]
        )) {

            $latestStatus[
                $r['box_name']
            ] = $r;
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

    $val = strtolower(
        (string) $status['lock_status']
    );

    if (
        in_array(
            $val,
            [
                '1',
                'locked',
                'true',
                'closed'
            ],
            true
        )
    ) {
        return 'locked';
    }

    if (
        in_array(
            $val,
            [
                '0',
                'unlocked',
                'false',
                'open'
            ],
            true
        )
    ) {
        return 'unlocked';
    }

    return 'unknown';
}


// =========================================================
// LOCKED DURATION
// =========================================================

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


// =========================================================
// KEEP ONLY CURRENTLY LOCKED BOXES
// =========================================================

$registeredBoxes = array_values(
    array_filter(
        $registeredBoxes,
        function ($box) use ($latestStatus) {

            return box_state(
                $latestStatus[
                    $box['box_id']
                ] ?? null
            ) === 'locked';
        }
    )
);


// =========================================================
// LOAD BOX DETAILS + AVATAR
// =========================================================

$boxDetails = [];

if (!empty($registeredBoxes)) {

    $boxIds = array_column(
        $registeredBoxes,
        'box_id'
    );

    $placeholders = implode(
        ',',
        array_fill(
            0,
            count($boxIds),
            '?'
        )
    );

    $stmt = $pdo->prepare("
        SELECT
            box_id,
            name_top,
            name_sub,
            box_content,
            target_open_date,
            avatar_path

        FROM user_details

        WHERE box_id IN ($placeholders)
    ");

    $stmt->execute($boxIds);

    foreach (
        $stmt->fetchAll(PDO::FETCH_ASSOC)
        as $d
    ) {

        $boxDetails[
            $d['box_id']
        ] = $d;
    }
}

?>

<style>

.box-status-page {
    --bg: #000000;
    --surface: #1c1f24;
    --border: #2b2f36;
    --text: #e9e7e2;
    --text-muted: #888d96;
    --locked: #ff1515;

    background: var(--bg);
    color: var(--text);

    min-height: 100vh;
}


.status-wrapper {
    max-width: 980px;

    margin: 0 auto;

    padding:
        48px
        24px
        64px;
}


.status-header {
    display: flex;

    align-items: baseline;
    justify-content: space-between;

    margin-bottom: 28px;
}


.status-header h1 {
    font-size: 20px;
    font-weight: 600;

    letter-spacing: -0.01em;

    margin: 0;
}


.status-count {
    font-size: 13px;
    color: var(--text-muted);
}


.status-empty {
    border:
        1px dashed
        var(--border);

    border-radius: 6px;

    padding: 32px;

    text-align: center;

    color: var(--text-muted);

    font-size: 14px;
}


.box-grid {
    display: grid;

    grid-template-columns:
        repeat(
            auto-fill,
            minmax(190px, 1fr)
        );

    gap: 10px;
}


.box-tile {
    background: var(--surface);

    border:
        1px solid
        var(--border);

    border-radius: 4px;

    padding:
        12px
        14px;

    display: flex;

    flex-direction: column;

    gap: 6px;
}


/* =====================================================
   TOP AREA
   Avatar + Box ID
   ===================================================== */

.box-top {
    display: flex;

    align-items: center;

    gap: 9px;

    min-width: 0;
}


.box-avatar {
    width: 34px;
    height: 34px;

    border-radius: 50%;

    object-fit: cover;

    flex-shrink: 0;

    border:
        1px solid
        rgba(255, 255, 255, 0.12);

    background:
        rgba(255, 255, 255, 0.06);
}


.box-avatar-placeholder {
    width: 34px;
    height: 34px;

    border-radius: 50%;

    flex-shrink: 0;

    background:
        rgba(255, 255, 255, 0.06);

    border:
        1px solid
        rgba(255, 255, 255, 0.12);
}


.box-id {
    font-family:
        ui-monospace,
        SFMono-Regular,
        Menlo,
        Consolas,
        monospace;

    font-size: 13px;

    color: var(--text);

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


.box-details-info {
    display: flex;

    flex-direction: column;

    gap: 2px;

    font-size: 11px;

    color: var(--text-muted);
}


.box-details-info span {
    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


.box-meta {
    margin-top: auto;

    padding-top: 4px;
}


.box-state {
    display: inline-flex;

    align-items: center;

    gap: 6px;

    font-size: 12px;

    color: var(--locked);
}


.box-state svg {
    width: 12px;
    height: 12px;

    flex-shrink: 0;
}

</style>


<div class="status-wrapper">

    <div class="status-header">

        <h1>
            Locked Users
        </h1>

    </div>


    <?php if (empty($registeredBoxes)): ?>

        <p class="status-empty">
            No locked boxes right now.
        </p>

    <?php else: ?>

        <div class="box-grid">

            <?php foreach (
                $registeredBoxes as $box
            ):

                $id =
                    $box['box_id'];

                $status =
                    $latestStatus[$id]
                    ?? null;

                $d =
                    $boxDetails[$id]
                    ?? null;

            ?>

                <div
                    class="box-tile"
                    data-state="locked"
                    data-box-id="<?= htmlspecialchars($id) ?>"
                >

                    <!-- =================================
                         BOX HEADER
                         ================================= -->

                    <div class="box-top">

                        <?php if (
                            $d &&
                            !empty($d['avatar_path'])
                        ): ?>

                            <img
                                class="box-avatar"
                                src="<?= htmlspecialchars($d['avatar_path']) ?>"
                                alt=""
                                loading="lazy"
                            >

                        <?php else: ?>

                            <div
                                class="box-avatar-placeholder"
                                aria-hidden="true"
                            ></div>

                        <?php endif; ?>


                        <span class="box-id">
                            LockMeBox
                            <?= htmlspecialchars($id) ?>
                        </span>

                    </div>


                    <!-- =================================
                         BOX DETAILS
                         ================================= -->

                    <?php if ($d): ?>

                        <div class="box-details-info">

                            <?php if (
                                !empty($d['name_top'])
                            ): ?>

                                <span>
                                    Keyholder:
                                    <?= htmlspecialchars(
                                        $d['name_top']
                                    ) ?>
                                </span>

                            <?php endif; ?>


                            <?php if (
                                !empty($d['name_sub'])
                            ): ?>

                                <span>
                                    Lockee:
                                    <?= htmlspecialchars(
                                        $d['name_sub']
                                    ) ?>
                                </span>

                            <?php endif; ?>


                            <?php if (
                                !empty($d['box_content'])
                            ): ?>

                                <span>
                                    Box Content:
                                    <?= htmlspecialchars(
                                        $d['box_content']
                                    ) ?>
                                </span>

                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $d['target_open_date']
                                )
                            ): ?>

                                <span>
                                    Target Open Date:
                                    <?= htmlspecialchars(
                                        date(
                                            'd.m.Y H:i',
                                            strtotime(
                                                $d[
                                                    'target_open_date'
                                                ]
                                            )
                                        )
                                    ) ?>
                                </span>

                            <?php endif; ?>

                        </div>

                    <?php endif; ?>


                    <!-- =================================
                         STATUS
                         ================================= -->

                    <span class="box-meta">

                        <span class="box-state">

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
                                    $status[
                                        'created_at'
                                    ] ?? null
                                )
                            ) ?>

                        </span>

                    </span>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>


<?php

require_once __DIR__ . '/templates/footer.php';

?>