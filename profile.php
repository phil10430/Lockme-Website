<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/profile_helpers.php';
require_once __DIR__ . '/includes/profile_fields.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php'); exit;
}
$user_id  = (int)$_SESSION['id'];
$username = (string)($_SESSION['username'] ?? '');
$errors = []; $success = [];

// =========================================================
// REMOTE ACCESS (grant / revoke), owner only
// =========================================================
if (empty($_SESSION['remote_access_csrf'])) {
    $_SESSION['remote_access_csrf'] = bin2hex(random_bytes(32));
}
$remoteAccessCsrf = $_SESSION['remote_access_csrf'];

if (isset($_POST['grantRemoteAccess'], $_POST['boxId'], $_POST['username']) ||
    isset($_POST['revokeRemoteAccess'], $_POST['boxId'], $_POST['targetUserId'])) {
    if (!hash_equals($remoteAccessCsrf, (string)($_POST['csrf_token'] ?? ''))) {
        profileJson(['success' => false, 'message' => 'Invalid security token.'], 403);
    }
    $boxId = trim((string)$_POST['boxId']);
    if (!preg_match('/^\d+$/D', $boxId)) {
        profileJson(['success' => false, 'message' => 'Invalid box ID.'], 400);
    }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT user_id FROM user_boxes WHERE box_id = ? FOR UPDATE');
        $stmt->execute([$boxId]);
        if ((int)$stmt->fetchColumn() !== $user_id) {
            $pdo->rollBack();
            profileJson(['success' => false, 'message' => 'Only the box owner can manage remote access.'], 403);
        }

        if (isset($_POST['grantRemoteAccess'])) {
            $targetUsername = trim((string)$_POST['username']);
            if ($targetUsername === '' || mb_strlen($targetUsername) > 255) {
                $pdo->rollBack();
                profileJson(['success' => false, 'message' => 'Invalid username.'], 400);
            }
            $stmt = $pdo->prepare('SELECT id, username FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$targetUsername]);
            $target = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$target) {
                $pdo->rollBack();
                profileJson(['success' => false, 'message' => 'User not found.'], 404);
            }
            if ((int)$target['id'] === $user_id) {
                $pdo->rollBack();
                profileJson(['success' => false, 'message' => 'The owner already has access.'], 400);
            }
            $stmt = $pdo->prepare('SELECT id FROM box_permissions WHERE box_id = ? AND user_id = ? FOR UPDATE');
            $stmt->execute([$boxId, $target['id']]);
            if ($stmt->fetchColumn()) {
                $stmt = $pdo->prepare('UPDATE box_permissions SET revoked_at = NULL, granted_by = ?, created_at = NOW() WHERE box_id = ? AND user_id = ?');
                $stmt->execute([$user_id, $boxId, $target['id']]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO box_permissions (box_id, user_id, granted_by, created_at, revoked_at) VALUES (?, ?, ?, NOW(), NULL)');
                $stmt->execute([$boxId, $target['id'], $user_id]);
            }
            $pdo->commit();
            profileJson(['success' => true, 'message' => 'Remote access granted.', 'user_id' => (int)$target['id'], 'username' => $target['username']]);
        }

        $targetUserId = filter_var($_POST['targetUserId'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($targetUserId === false) {
            $pdo->rollBack();
            profileJson(['success' => false, 'message' => 'Invalid user ID.'], 400);
        }
        $stmt = $pdo->prepare('UPDATE box_permissions SET revoked_at = NOW() WHERE box_id = ? AND user_id = ? AND revoked_at IS NULL');
        $stmt->execute([$boxId, $targetUserId]);
        $revoked = $stmt->rowCount() > 0;
        if ($revoked) {
            $stmt = $pdo->prepare("UPDATE box_commands SET status = 'cancelled', completed_at = NOW(), result = 'Remote access revoked' WHERE box_id = ? AND requested_by = ? AND status = 'pending'");
            $stmt->execute([$boxId, $targetUserId]);
        }
        $pdo->commit();
        profileJson(['success' => $revoked, 'message' => $revoked ? 'Remote access revoked.' : 'Active permission not found.'], $revoked ? 200 : 404);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log($e->getMessage());
        profileJson(['success' => false, 'message' => 'Could not update remote access.'], 500);
    }
}

// =========================================================
// REMOVE BOX
// =========================================================
if (isset($_POST['removeBox'])) {
    $boxId = trim((string)($_POST['boxId'] ?? ''));
    if (!preg_match('/^\d{6}$/', $boxId)) profileJson(['success' => false, 'message' => 'Invalid box ID.'], 400);
    try {
        $stmt = $pdo->prepare('DELETE FROM user_boxes WHERE user_id = ? AND box_id = ?');
        $stmt->execute([$user_id, $boxId]);
        $ok = $stmt->rowCount() > 0;
        profileJson(['success' => $ok, 'message' => $ok ? 'Box removed.' : 'Box not found.']);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        profileJson(['success' => false, 'message' => 'Could not remove box.'], 500);
    }
}

// =========================================================
// SAVE BOX DETAILS (only while box is open; keyholder is set automatically)
// =========================================================
if (isset($_POST['saveBoxDetails'])) {
    $boxId   = trim((string)($_POST['boxId'] ?? ''));
    $content = trim((string)($_POST['box_content'] ?? ''));
    if (!preg_match('/^\d{6}$/', $boxId) || mb_strlen($content) > 255)
        profileJson(['success' => false, 'message' => 'Invalid box details.'], 400);
    try {
        $stmt = $pdo->prepare('SELECT 1 FROM user_boxes WHERE user_id=? AND box_id=?');
        $stmt->execute([$user_id, $boxId]);
        if (!$stmt->fetchColumn()) profileJson(['success' => false, 'message' => 'Box not found.'], 403);

        $stmt = $pdo->prepare('SELECT lock_status, open_time, protection_level_timer FROM box_data_actual WHERE box_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$boxId]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if (is_effectively_locked($current))
            profileJson(['success' => false, 'message' => 'Box details can only be changed while the box is open.'], 409);

        $stmt = $pdo->prepare("INSERT INTO box_details (box_id, keyholder, box_content) VALUES (?, '', ?)
                               ON DUPLICATE KEY UPDATE box_content = VALUES(box_content)");
        $stmt->execute([$boxId, $content !== '' ? $content : null]);
        profileJson(['success' => true]);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        profileJson(['success' => false, 'message' => 'Could not save box details.'], 500);
    }
}

// =========================================================
// SAVE PROFILE
// =========================================================
if (isset($_POST['saveUserDetails'])) {
    $newUsername = trim((string)($_POST['username'] ?? ''));
    $birth       = trim((string)($_POST['birth_date'] ?? ''));
    $visibility  = (string)($_POST['profile_visibility'] ?? 'private');
    $socialX     = trim((string)($_POST['social_x'] ?? ''));
    $socialI     = trim((string)($_POST['social_instagram'] ?? ''));
    $socialB     = trim((string)($_POST['social_bluesky'] ?? ''));

    if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/D', $newUsername))
        profileJson(['success' => false, 'message' => 'Username must contain 3–50 letters, numbers or underscores.'], 400);
    if (!in_array($visibility, ['private', 'public'], true))
        profileJson(['success' => false, 'message' => 'Invalid profile values.'], 400);
    if ($birth !== '') {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $birth);
        if (!$date || $date->format('Y-m-d') !== $birth || $date > new DateTimeImmutable('today'))
            profileJson(['success' => false, 'message' => 'Invalid birth date.'], 400);
    }
    if (!profileSocialValid($socialX, ['x.com', 'www.x.com', 'twitter.com', 'www.twitter.com']) ||
        !profileSocialValid($socialI, ['instagram.com', 'www.instagram.com']) ||
        !profileSocialValid($socialB, ['bsky.app', 'www.bsky.app']))
        profileJson(['success' => false, 'message' => 'Invalid social profile URL (HTTPS required).'], 400);

    // Schema-driven fields (headline, role, ...)
    $fieldError = null;
    $fields = profileCollectFields($_POST['profile'] ?? [], $fieldError);
    if ($fieldError !== null) profileJson(['success' => false, 'message' => $fieldError], 400);

    $newAvatar = null;
    try {
        $newAvatar = profileAvatarUpload($user_id);
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('SELECT avatar_path FROM user_details WHERE user_id=? FOR UPDATE');
        $stmt->execute([$user_id]);
        $oldAvatar = $stmt->fetchColumn() ?: null;

        $stmt = $pdo->prepare('SELECT username, username_changed_at FROM users WHERE id=? FOR UPDATE');
        $stmt->execute([$user_id]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$currentUser) throw new RuntimeException('User not found.');

        if ($newUsername !== $currentUser['username']) {
            if (!empty($currentUser['username_changed_at'])) {
                $nextChange = (new DateTimeImmutable($currentUser['username_changed_at']))->modify('+30 days');
                if (new DateTimeImmutable() < $nextChange)
                    throw new RuntimeException('You can change your username again on ' . $nextChange->format('d.m.Y') . '.');
            }
            $stmt = $pdo->prepare('UPDATE users SET username=?, username_changed_at=NOW() WHERE id=?');
            $stmt->execute([$newUsername, $user_id]);
        }

        // Fixed columns + schema columns (column names come from the schema, never from the request)
        $cols = [
            'social_x'          => $socialX ?: null,
            'social_instagram'  => $socialI ?: null,
            'social_bluesky'    => $socialB ?: null,
            'birth_date'        => $birth ?: null,
            'profile_visibility'=> $visibility,
            'avatar_path'       => $newAvatar,
        ] + $fields;

        $names   = array_keys($cols);
        $insert  = '`user_id`, ' . implode(', ', array_map(fn($c) => "`$c`", $names));
        $holders = implode(', ', array_fill(0, count($names) + 1, '?'));
        $updates = implode(', ', array_map(
            fn($c) => $c === 'avatar_path'
                ? '`avatar_path` = COALESCE(VALUES(`avatar_path`), `avatar_path`)'
                : "`$c` = VALUES(`$c`)",
            $names
        ));
        $stmt = $pdo->prepare("INSERT INTO user_details ($insert) VALUES ($holders) ON DUPLICATE KEY UPDATE $updates");
        $stmt->execute(array_merge([$user_id], array_values($cols)));

        $pdo->commit();
        $_SESSION['username'] = $newUsername;
        if ($newAvatar) profileDeleteOwnedAvatar($oldAvatar);
        profileJson(['success' => true, 'message' => 'Profile updated.', 'avatar_path' => $newAvatar ?: $oldAvatar, 'username' => $newUsername]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if ($newAvatar) profileDeleteOwnedAvatar($newAvatar);
        if ($e instanceof PDOException && $e->getCode() === '23000')
            profileJson(['success' => false, 'message' => 'Username is already taken.'], 409);
        error_log($e->getMessage());
        profileJson(['success' => false, 'message' => $e instanceof RuntimeException ? $e->getMessage() : 'Could not save profile.'], 500);
    }
}

// =========================================================
// LOAD USER
// =========================================================
$stmt = $pdo->prepare(
    'SELECT u.username, u.email, u.created_at,
            d.avatar_path, d.social_x, d.social_instagram, d.social_bluesky, d.birth_date, d.profile_visibility, '
    . profileFieldColumnsSql('d') . '
     FROM users u LEFT JOIN user_details d ON d.user_id = u.id WHERE u.id = ?'
);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { header('Location: index.php'); exit; }
$username = $user['username'];

// =========================================================
// PASSWORD / E-MAIL / DELETE ACCOUNT
// =========================================================
if (isset($_POST['changePassword'])) {
    $stmt = $pdo->prepare('SELECT password FROM users WHERE id=?'); $stmt->execute([$user_id]); $hash = $stmt->fetchColumn();
    if (!$hash || !password_verify((string)($_POST['current_password'] ?? ''), $hash)) $errors['password'] = 'Current password is incorrect.';
    elseif (strlen((string)($_POST['new_password'] ?? '')) < 6) $errors['password'] = 'New password must be at least 6 characters.';
    elseif (($_POST['new_password'] ?? '') !== ($_POST['confirm_new_password'] ?? '')) $errors['password'] = 'Passwords do not match.';
    else {
        $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), $user_id]);
        $success['password'] = 'Password updated successfully.';
    }
}
if (isset($_POST['changeEmail'])) {
    $stmt = $pdo->prepare('SELECT password FROM users WHERE id=?'); $stmt->execute([$user_id]); $hash = $stmt->fetchColumn();
    $email = trim((string)($_POST['new_email'] ?? ''));
    if (!$hash || !password_verify((string)($_POST['email_password'] ?? ''), $hash)) $errors['email'] = 'Password is incorrect.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid e-mail address.';
    else {
        $pdo->prepare('UPDATE users SET email=? WHERE id=?')->execute([$email, $user_id]);
        $user['email'] = $email;
        $success['email'] = 'E-Mail updated successfully.';
    }
}
if (isset($_POST['deleteAccount'])) {
    $stmt = $pdo->prepare('SELECT password FROM users WHERE id=?'); $stmt->execute([$user_id]); $hash = $stmt->fetchColumn();
    if (!$hash || !password_verify((string)($_POST['delete_password'] ?? ''), $hash)) $errors['delete'] = 'Password is incorrect.';
    else {
        $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$user_id]);
        session_destroy();
        header('Location: index.php'); exit;
    }
}

// =========================================================
// BOX DATA FOR THE PAGE
// =========================================================
$boxData           = profileLoadBoxData($pdo, $user_id);
$registeredBoxes   = $boxData['registeredBoxes'];
$remoteAccessByBox = $boxData['remoteAccessByBox'];
$sharedWithMe      = $boxData['sharedWithMe'];
$boxHistory        = $boxData['boxHistory'];
$boxDetails        = $boxData['boxDetails'];
$boxActual         = $boxData['boxActual'];