<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/profile_helpers.php';
require_once __DIR__ . '/includes/profile_view.php';

$bodyClass = 'box-status-page';
$boxId = trim((string) ($_GET['box_id'] ?? ''));
$box = null;
$status = null;
$keyholderAvatar = '';
$keyholderLink = '';
$age = null;
$facts = [];
$isPro = false;

if (preg_match('/^\d+$/D', $boxId)) {
    // Visibility is user-wide. Private profiles must not expose box details.
    $stmt = $pdo->prepare("
        SELECT ub.box_id, ub.user_id, u.username, u.pro_version,
               ud.avatar_path, ud.birth_date,
               COALESCE(ku.username, bd.keyholder) AS keyholder,
               bd.keyholder_user_id, bd.box_content,
               " . profileFieldColumnsSql('ud') . "
        FROM user_boxes ub
        JOIN users u ON u.id = ub.user_id
        JOIN user_details ud ON ud.user_id = u.id
        LEFT JOIN box_details bd ON bd.box_id = ub.box_id
        LEFT JOIN users ku ON ku.id = bd.keyholder_user_id
        WHERE ub.box_id = :box_id
          AND ud.profile_visibility = 'public'
        LIMIT 1
    ");
    $stmt->execute(['box_id' => $boxId]);
    $box = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    if ($box) {
        $stmt = $pdo->prepare("
            SELECT lock_status, open_time, protection_level_timer, created_at
            FROM box_data_history
            WHERE box_id = :box_id
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute(['box_id' => $boxId]);
        $status = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        // Keyholder avatar (and link): only if the keyholder's own profile is public.
        // Lookup by user id, fall back to the name for old entries.
        $keyholderName = trim((string) ($box['keyholder'] ?? ''));
        if ($keyholderName !== '') {
            if (!empty($box['keyholder_user_id'])) {
                $stmt = $pdo->prepare("
                    SELECT u.id, ud.avatar_path
                    FROM users u
                    JOIN user_details ud ON ud.user_id = u.id
                    WHERE u.id = :id AND ud.profile_visibility = 'public'
                    LIMIT 1
                ");
                $stmt->execute(['id' => (int) $box['keyholder_user_id']]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT u.id, ud.avatar_path
                    FROM users u
                    JOIN user_details ud ON ud.user_id = u.id
                    WHERE u.username = :username AND ud.profile_visibility = 'public'
                    LIMIT 1
                ");
                $stmt->execute(['username' => $keyholderName]);
            }
            $kh = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($kh) {
                $keyholderAvatar = (string) ($kh['avatar_path'] ?? '');

                // Link to the keyholder's own box page, if he has one
                $stmt = $pdo->prepare("SELECT box_id FROM user_boxes WHERE user_id = :id ORDER BY registered_at DESC LIMIT 1");
                $stmt->execute(['id' => (int) $kh['id']]);
                $khBox = $stmt->fetchColumn();
                if ($khBox !== false && $khBox !== null) {
                    $keyholderLink = 'box_share.php?box_id=' . rawurlencode((string) $khBox);
                }
            }
        }

        $age   = profileAge($box['birth_date'] ?? null);
        $facts = profileBuildFacts($box);
        $isPro = (string) ($box['pro_version'] ?? '0') === '1';
    }
}

if (!$box) {
    http_response_code(404);
}

function shared_box_locked(?array $status): bool
{
    if (!$status) return false;
    $state = strtolower((string) ($status['lock_status'] ?? ''));
    if (!in_array($state, ['1', 'locked', 'true', 'closed'], true)) return false;
    if (!empty($status['protection_level_timer']) && !empty($status['open_time'])) {
        $opens = strtotime((string) $status['open_time']);
        if ($opens !== false && $opens <= time()) return false;
    }
    return true;
}

function shared_box_duration(?string $since): string
{
    if (!$since || ($time = strtotime($since)) === false) return '';
    $seconds = max(0, time() - $time);
    $days = intdiv($seconds, 86400);
    $hours = intdiv($seconds % 86400, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    $parts = [];
    if ($days) $parts[] = $days . 'd';
    if ($hours) $parts[] = $hours . 'h';
    if ($minutes || !$parts) $parts[] = $minutes . 'm';
    return implode(' ', $parts);
}

$isLocked = shared_box_locked($status);
$stateKnown = $status && in_array(strtolower((string) $status['lock_status']),
    ['1', '0', 'locked', 'unlocked', 'true', 'false', 'closed', 'open'], true);

require_once __DIR__ . '/templates/header.php';
?>
<link rel="stylesheet" href="/assets/css/box_share.css">
<link rel="stylesheet" href="/assets/css/profile_view.css">

<div class="settings-wrapper">
    <div class="settings-container">
        <?php if (!$box): ?>
            <p class="box-empty">This box's status isn't shared publicly.</p>
        <?php else: ?>
            <div class="settings-card">
                <div class="settings-card-body">
                    <div class="box-item">
                        <div class="box-item-main">

                            <div class="box-title-row">
                                <?php if (!empty($box['avatar_path'])): ?>
                                    <img class="box-list-avatar"
                                         src="<?= htmlspecialchars((string) $box['avatar_path'], ENT_QUOTES, 'UTF-8') ?>"
                                         alt="Profile avatar" loading="lazy">
                                <?php else: ?>
                                    <div class="box-list-avatar-placeholder" aria-hidden="true"></div>
                                <?php endif; ?>

                                <div class="box-title-content">
                                    <span class="lockee-title"><?= htmlspecialchars((string) $box['username'], ENT_QUOTES, 'UTF-8') ?><?php if ($age !== null): ?><span class="lockee-age">, <?= (int) $age ?></span><?php endif; ?><?php if ($isPro): ?><span class="share-pro" title="Pro member">PRO</span><?php endif; ?></span>
                                </div>

                                <?php if (!empty($box['headline'])): ?>
                                    <p class="share-headline"><?= htmlspecialchars((string) $box['headline'], ENT_QUOTES, 'UTF-8') ?></p>
                                <?php endif; ?>

                                <span class="box-id">LockMeBox #<?= htmlspecialchars($boxId, ENT_QUOTES, 'UTF-8') ?></span>
                            </div>

                            <?php if ($facts): ?>
                                <div class="share-chips">
                                    <?php foreach ($facts as $fact): ?>
                                        <span class="share-chip">
                                            <small><?= htmlspecialchars($fact['label'], ENT_QUOTES, 'UTF-8') ?></small>
                                            <?= htmlspecialchars($fact['value'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?= profileRenderExtras($box, []) ?>

                            <?php if ($isLocked && !empty($box['keyholder'])): ?>
                                <div class="box-lock-relation">
                                    <span class="lockee-name"><?= htmlspecialchars((string) $box['username'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="relation-text">is locked by</span>
                                    <?php if ($keyholderLink !== ''): ?>
                                        <a class="keyholder-wrap keyholder-link" href="<?= htmlspecialchars($keyholderLink, ENT_QUOTES, 'UTF-8') ?>">
                                    <?php else: ?>
                                        <span class="keyholder-wrap">
                                    <?php endif; ?>
                                        <?php if ($keyholderAvatar !== ''): ?>
                                            <img class="keyholder-avatar"
                                                 src="<?= htmlspecialchars($keyholderAvatar, ENT_QUOTES, 'UTF-8') ?>"
                                                 alt="" loading="lazy">
                                        <?php endif; ?>
                                        <span class="keyholder-name"><?= htmlspecialchars((string) $box['keyholder'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if ($keyholderLink !== ''): ?>
                                        </a>
                                    <?php else: ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($box['box_content'])): ?>
                                <div class="box-content-tag">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.8" stroke-linecap="round"
                                         stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="7.5" cy="15.5" r="5.5"></circle>
                                        <path d="m21 2-9.6 9.6"></path>
                                        <path d="m15.5 7.5 3 3"></path>
                                        <path d="m18.5 4.5 3 3"></path>
                                    </svg>
                                    <span><?= htmlspecialchars((string) $box['box_content'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="box-lock-status">
                                <?php if ($isLocked): ?>
                                    <svg class="box-lock-icon" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                                        <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                                    </svg>
                                    <span>Locked</span>
                                    <?php if (!empty($status['created_at'])): ?>
                                        <span class="box-lock-duration">
                                            for <?= htmlspecialchars(shared_box_duration($status['created_at']), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    <?php endif; ?>
                                <?php elseif ($stateKnown): ?>
                                    <span>Open</span>
                                <?php else: ?>
                                    <span>Status unknown</span>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/templates/footer.php'; ?>