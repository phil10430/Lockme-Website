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
// TOTAL LOCKED COUNT (all boxes, regardless of public_status)
// =========================================================

$totalLockedCount = 0;

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM box_data_actual WHERE lock_status = 1");
    $stmt->execute();
    $totalLockedCount = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    $totalLockedCount = 0; // Fallback, falls DB-Fehler
}

// =========================================================
// LATEST STATUS PER BOX
// =========================================================

$latestStatus = [];

if (!empty($registeredBoxes)) {
    $boxIds = array_column($registeredBoxes, 'box_id');
    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));

    $stmt = $pdo->prepare("
        SELECT box_name, lock_status, open_time, created_at, protection_level_timer, protection_level_password
        FROM box_data_history
        WHERE box_name IN ($placeholders)
        ORDER BY created_at DESC
    ");
    $stmt->execute($boxIds);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        if (!isset($latestStatus[$r['box_name']])) {
            $latestStatus[$r['box_name']] = $r;
        }
    }
}


// =========================================================
// BOX STATE
// =========================================================

function box_state(?array $status): string
{
    if ($status === null) return 'unknown';

    $val = strtolower((string)$status['lock_status']);
    if (in_array($val, ['1', 'locked', 'true', 'closed'], true)) return 'locked';
    if (in_array($val, ['0', 'unlocked', 'false', 'open'], true)) return 'unlocked';
    return 'unknown';
}

// A box counts as "effectively locked" only if lock_status says
// locked AND, when timer-protected, the timer hasn't expired yet.
// A box with lock_status = 1 whose timer has already run out is
// treated as open, since the box would physically release itself.
function is_effectively_locked(?array $status): bool
{
    if (box_state($status) !== 'locked') {
        return false;
    }

    if (!empty($status['protection_level_timer']) && !empty($status['open_time'])) {
        $openTime = strtotime($status['open_time']);
        if ($openTime !== false && $openTime <= time()) {
            return false; // timer has expired - box is effectively open
        }
    }

    return true;
}


// =========================================================
// LOCKED DURATION
// =========================================================

function locked_duration(?string $lockedSince): string
{
    if (!$lockedSince) return '';

    $diff = max(0, time() - strtotime($lockedSince));
    $days = intdiv($diff, 86400);
    $hours = intdiv($diff % 86400, 3600);
    $minutes = intdiv($diff % 3600, 60);

    $parts = [];
    if ($days > 0)  $parts[] = $days . 'd';
    if ($hours > 0) $parts[] = $hours . 'h';
    if ($minutes > 0 || empty($parts)) $parts[] = $minutes . 'm';

    return implode(' ', $parts);
}


// =========================================================
// KEEP ONLY CURRENTLY LOCKED BOXES
// =========================================================

$registeredBoxes = array_values(array_filter($registeredBoxes, function ($box) use ($latestStatus) {
    return is_effectively_locked($latestStatus[$box['box_id']] ?? null);
}));


// =========================================================
// LOAD BOX DETAILS + AVATAR
// =========================================================

$boxDetails = [];

if (!empty($registeredBoxes)) {
    $boxIds = array_column($registeredBoxes, 'box_id');
    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));

    $stmt = $pdo->prepare("
        SELECT box_id, name_top, name_sub, box_content, avatar_path, public_status
        FROM user_details
        WHERE box_id IN ($placeholders)
    ");
    $stmt->execute($boxIds);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $d) {
        $boxDetails[$d['box_id']] = $d;
    }
}
?>

<style>
/* Scoped layout override for this page only - reuses all
   existing classes/content from profile_page.php, just
   arranges the same box-items as a responsive grid instead
   of a stacked list, and tightens their spacing. */

.box-status-page .box-list {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}

@media (min-width: 769px) {
    .box-status-page .box-list {
        grid-template-columns: repeat(3, 1fr);
    }
}

.box-status-page .box-item {
    flex-direction: column;
    align-items: flex-start;
    padding: 10px 12px;
    gap: 4px;
}

.box-status-page .box-item-main {
    gap: 3px;
}
</style>

<div class="settings-wrapper">

    <div class="settings-container">

        <div class="settings-hero">
            <div>
                <h1>Lockees</h1>
                <p><?= $totalLockedCount ?> locked total &middot; <?= count($registeredBoxes) ?> public</p>
            </div>
        </div>

        <div class="settings-card">

            <div class="settings-card-body">

                <?php if (empty($registeredBoxes)): ?>

                    <p class="box-empty">No locked boxes right now.</p>

                <?php else: ?>

                    <div class="box-list">

                        <?php foreach ($registeredBoxes as $box):
                            $id = $box['box_id'];
                            $status = $latestStatus[$id] ?? null;
                            $d = $boxDetails[$id] ?? null;
                            $isLocked = true; // already filtered to locked-only above
                            $actual = ['locked_since' => $status['created_at'] ?? null];
                        ?>

                            <div class="box-item" id="box-<?= htmlspecialchars($id) ?>">

                                <div class="box-item-main">

                                    <div class="box-title-row">
                                        <?php if ($d && !empty($d['avatar_path'])): ?>
                                            <img class="box-list-avatar" src="<?= htmlspecialchars($d['avatar_path']) ?>" alt="" loading="lazy">
                                        <?php else: ?>
                                            <div class="box-list-avatar-placeholder" aria-hidden="true"></div>
                                        <?php endif; ?>

                                        <span class="box-id">LockMeBox <?= htmlspecialchars($id) ?></span>
                                    </div>

                                    <?php if ($d && (!empty($d['name_top']) || !empty($d['name_sub']))): ?>
                                        <div class="box-relation-row">

                                            <?php if (!empty($d['name_top'])): ?>
                                                <span class="box-relation-top"><?= htmlspecialchars($d['name_top']) ?></span>
                                            <?php endif; ?>

                                            <?php if (!empty($d['name_top']) && !empty($d['name_sub'])): ?>
                                                <div class="box-relation-sub-row">
                                                    <svg class="box-relation-icon"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        aria-label="locked for">
                                                        <path d="M5 12h14"></path>
                                                        <path d="m13 6 6 6-6 6"></path>
                                                    </svg>
                                                    <span><?= htmlspecialchars($d['name_sub']) ?></span>
                                                </div>
                                            <?php elseif (!empty($d['name_sub'])): ?>
                                                <span><?= htmlspecialchars($d['name_sub']) ?></span>
                                            <?php endif; ?>

                                        </div>
                                    <?php endif; ?>

                                    <?php if ($d && !empty($d['box_content'])): ?>
                                        <div class="box-content-tag">
                                            <svg viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true">
                                                <circle cx="7.5" cy="15.5" r="5.5"></circle>
                                                <path d="m21 2-9.6 9.6"></path>
                                                <path d="m15.5 7.5 3 3"></path>
                                                <path d="m18.5 4.5 3 3"></path>
                                            </svg>
                                            <span><?= htmlspecialchars($d['box_content']) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($isLocked && !empty($actual['locked_since'])): ?>
                                        <div class="box-lock-status">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                                                <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                                            </svg>
                                            Locked since <?= htmlspecialchars(locked_duration($actual['locked_since'])) ?>
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