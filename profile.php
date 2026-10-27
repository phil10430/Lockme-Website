<?php
require_once __DIR__ . '/includes/config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php'); exit;
}
$user_id = (int)$_SESSION['id'];
$username = (string)($_SESSION['username'] ?? '');
$errors = []; $success = [];

function profileSocialValid(string $url, array $hosts): bool {
    if ($url === '') return true;
    if (strlen($url) > 255 || !filter_var($url, FILTER_VALIDATE_URL)) return false;
    $p = parse_url($url);
    return is_array($p) && strtolower($p['scheme'] ?? '') === 'https'
        && in_array(strtolower($p['host'] ?? ''), $hosts, true)
        && !isset($p['user'], $p['pass']) && !isset($p['port'])
        && !isset($p['query']) && !isset($p['fragment']);
}
function profileJson(array $result, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result); exit;
}
function profileAvatarUpload(int $userId): ?string {
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES['avatar'];
    if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 2*1024*1024 || !is_uploaded_file($f['tmp_name']))
        throw new RuntimeException('Avatar upload failed or exceeds 2 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $types = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($types[$mime]) || @getimagesize($f['tmp_name']) === false)
        throw new RuntimeException('Only valid JPG, PNG and WebP images are allowed.');
    $dir = __DIR__ . '/uploads/user_avatars/';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir))
        throw new RuntimeException('Avatar directory could not be created.');
    $name = bin2hex(random_bytes(16)) . '.' . $types[$mime];
    if (!move_uploaded_file($f['tmp_name'], $dir . $name)) throw new RuntimeException('Could not save avatar.');
    return 'uploads/user_avatars/' . $name;
}
function profileDeleteOwnedAvatar(?string $path): void {
    if (!$path || !str_starts_with($path, 'uploads/user_avatars/')) return;
    $name = basename($path);
    if ($path !== 'uploads/user_avatars/' . $name) return;
    $file = __DIR__ . '/uploads/user_avatars/' . $name;
    if (is_file($file)) @unlink($file);
}

// Remote-access grants are independent of public profile visibility.
// Only the registered owner may manage grants.
if (empty($_SESSION['remote_access_csrf'])) {
    $_SESSION['remote_access_csrf'] = bin2hex(random_bytes(32));
}
$remoteAccessCsrf = $_SESSION['remote_access_csrf'];

if (isset($_POST['grantRemoteAccess'], $_POST['boxId'], $_POST['username']) ||
    isset($_POST['revokeRemoteAccess'], $_POST['boxId'], $_POST['targetUserId'])) {
    if (!hash_equals($remoteAccessCsrf, (string)($_POST['csrf_token'] ?? ''))) {
        profileJson(['success' => false, 'message' => 'Invalid security token.'], 403);
    }
    $boxId = trim((string) $_POST['boxId']);
    if (!preg_match('/^\d+$/D', $boxId)) {
        profileJson(['success' => false, 'message' => 'Invalid box ID.'], 400);
    }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT user_id FROM user_boxes WHERE box_id = ? FOR UPDATE');
        $stmt->execute([$boxId]);
        if ((int) $stmt->fetchColumn() !== $user_id) {
            $pdo->rollBack();
            profileJson(['success' => false, 'message' => 'Only the box owner can manage remote access.'], 403);
        }

        if (isset($_POST['grantRemoteAccess'])) {
            $targetUsername = trim((string) $_POST['username']);
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
            if ((int) $target['id'] === $user_id) {
                $pdo->rollBack();
                profileJson(['success' => false, 'message' => 'The owner already has access.'], 400);
            }
            // Works with or without a UNIQUE(box_id,user_id) index.
            $stmt = $pdo->prepare('SELECT id FROM box_permissions WHERE box_id = ? AND user_id = ? FOR UPDATE');
            $stmt->execute([$boxId, $target['id']]);
            $permissionId = $stmt->fetchColumn();
            if ($permissionId) {
                $stmt = $pdo->prepare('UPDATE box_permissions SET revoked_at = NULL, granted_by = ?, created_at = NOW() WHERE box_id = ? AND user_id = ?');
                $stmt->execute([$user_id, $boxId, $target['id']]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO box_permissions (box_id, user_id, granted_by, created_at, revoked_at) VALUES (?, ?, ?, NOW(), NULL)');
                $stmt->execute([$boxId, $target['id'], $user_id]);
            }
            $pdo->commit();
            profileJson(['success' => true, 'message' => 'Remote access granted.', 'user_id' => (int) $target['id'], 'username' => $target['username']]);
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
            // Cancel pending commands submitted by the revoked user for this box.
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

if (isset($_POST['removeBox'])) {
    $boxId = trim((string)($_POST['boxId'] ?? ''));
    if (!preg_match('/^\d{6}$/', $boxId)) profileJson(['success'=>false,'message'=>'Invalid box ID.'],400);
    try {
        $stmt=$pdo->prepare('DELETE FROM user_boxes WHERE user_id = ? AND box_id = ?');
        $stmt->execute([$user_id,$boxId]);
        profileJson(['success'=>$stmt->rowCount()>0,'message'=>$stmt->rowCount()>0?'Box removed.':'Box not found.']);
    } catch (Throwable $e) { error_log($e->getMessage()); profileJson(['success'=>false,'message'=>'Could not remove box.'],500); }
}

if (isset($_POST['saveBoxDetails'])) {
    $boxId=trim((string)($_POST['boxId']??''));
    $top=trim((string)($_POST['keyholder']??''));
    $content=trim((string)($_POST['box_content']??''));
    if (!preg_match('/^\d{6}$/',$boxId) || $top === '' || mb_strlen($top)>255 || mb_strlen($content)>255)
        profileJson(['success'=>false,'message'=>'Invalid box details.'],400);
    try {
        $stmt=$pdo->prepare('SELECT 1 FROM user_boxes WHERE user_id=? AND box_id=?');
        $stmt->execute([$user_id,$boxId]);
        if (!$stmt->fetchColumn()) profileJson(['success'=>false,'message'=>'Box not found.'],403);
        $stmt=$pdo->prepare('INSERT INTO box_details (box_id,keyholder,box_content) VALUES (?,?,?) ON DUPLICATE KEY UPDATE keyholder=VALUES(keyholder),box_content=VALUES(box_content)');
        $stmt->execute([$boxId,$top,$content!==''?$content:null]);
        profileJson(['success'=>true]);
    } catch(Throwable $e) {error_log($e->getMessage());profileJson(['success'=>false,'message'=>'Could not save box details.'],500);}
}

if (isset($_POST['saveUserDetails'])) {
    $newUsername=trim((string)($_POST['username']??''));
    $birth=trim((string)($_POST['birth_date']??''));
    $visibility=(string)($_POST['profile_visibility']??'private');
    $socialX=trim((string)($_POST['social_x']??''));
    $socialI=trim((string)($_POST['social_instagram']??''));
    $socialB=trim((string)($_POST['social_bluesky']??''));
    if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/D',$newUsername)) profileJson(['success'=>false,'message'=>'Username must contain 3–50 letters, numbers or underscores.'],400);
    if (!in_array($visibility,['private','public'],true))
        profileJson(['success'=>false,'message'=>'Invalid profile values.'],400);
    if ($birth !== '') {
        $date=DateTimeImmutable::createFromFormat('!Y-m-d',$birth);
        if (!$date || $date->format('Y-m-d')!==$birth || $date>new DateTimeImmutable('today'))
            profileJson(['success'=>false,'message'=>'Invalid birth date.'],400);
    }
    if (!profileSocialValid($socialX,['x.com','www.x.com','twitter.com','www.twitter.com']) ||
        !profileSocialValid($socialI,['instagram.com','www.instagram.com']) ||
        !profileSocialValid($socialB,['bsky.app','www.bsky.app']))
        profileJson(['success'=>false,'message'=>'Invalid social profile URL (HTTPS required).'],400);
    $newAvatar=null;
    try {
        $newAvatar=profileAvatarUpload($user_id);
        $pdo->beginTransaction();
        $stmt=$pdo->prepare('SELECT avatar_path FROM user_details WHERE user_id=? FOR UPDATE');
        $stmt->execute([$user_id]); $oldAvatar=$stmt->fetchColumn() ?: null;
        $stmt=$pdo->prepare('SELECT username, username_changed_at FROM users WHERE id=? FOR UPDATE');
        $stmt->execute([$user_id]);
        $currentUser=$stmt->fetch(PDO::FETCH_ASSOC);
        if (!$currentUser) throw new RuntimeException('User not found.');
        if ($newUsername !== $currentUser['username']) {
            if (!empty($currentUser['username_changed_at'])) {
                $nextChange=(new DateTimeImmutable($currentUser['username_changed_at']))->modify('+30 days');
                if (new DateTimeImmutable() < $nextChange) {
                    throw new RuntimeException('You can change your username again on '.$nextChange->format('d.m.Y').'.');
                }
            }
            $stmt=$pdo->prepare('UPDATE users SET username=?, username_changed_at=NOW() WHERE id=?');
            $stmt->execute([$newUsername,$user_id]);
        }
        $stmt=$pdo->prepare('INSERT INTO user_details (user_id,social_x,social_instagram,social_bluesky,birth_date,profile_visibility,avatar_path) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE social_x=VALUES(social_x),social_instagram=VALUES(social_instagram),social_bluesky=VALUES(social_bluesky),birth_date=VALUES(birth_date),profile_visibility=VALUES(profile_visibility),avatar_path=COALESCE(VALUES(avatar_path),avatar_path)');
        $stmt->execute([$user_id,$socialX?:null,$socialI?:null,$socialB?:null,$birth?:null,$visibility,$newAvatar]);
        $pdo->commit();
        $_SESSION['username']=$newUsername;
        if ($newAvatar) profileDeleteOwnedAvatar($oldAvatar);
        profileJson(['success'=>true,'message'=>'Profile updated.','avatar_path'=>$newAvatar ?: $oldAvatar,'username'=>$newUsername]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if ($newAvatar) profileDeleteOwnedAvatar($newAvatar);
        if ($e instanceof PDOException && $e->getCode()==='23000') profileJson(['success'=>false,'message'=>'Username is already taken.'],409);
        error_log($e->getMessage()); profileJson(['success'=>false,'message'=>$e instanceof RuntimeException?$e->getMessage():'Could not save profile.'],500);
    }
}

$stmt=$pdo->prepare('SELECT u.username,u.email,u.created_at,d.avatar_path,d.social_x,d.social_instagram,d.social_bluesky,d.birth_date,d.profile_visibility FROM users u LEFT JOIN user_details d ON d.user_id=u.id WHERE u.id=?');
$stmt->execute([$user_id]);
$user=$stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {header('Location: index.php');exit;}
$username=$user['username'];

if (isset($_POST['changePassword'])) {
    $stmt=$pdo->prepare('SELECT password FROM users WHERE id=?');$stmt->execute([$user_id]);$hash=$stmt->fetchColumn();
    if (!$hash || !password_verify((string)($_POST['current_password']??''),$hash)) $errors['password']='Current password is incorrect.';
    elseif (strlen((string)($_POST['new_password']??''))<6) $errors['password']='New password must be at least 6 characters.';
    elseif (($_POST['new_password']??'')!==($_POST['confirm_new_password']??'')) $errors['password']='Passwords do not match.';
    else {$pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($_POST['new_password'],PASSWORD_DEFAULT),$user_id]);$success['password']='Password updated successfully.';}
}
if (isset($_POST['changeEmail'])) {
    $stmt=$pdo->prepare('SELECT password FROM users WHERE id=?');$stmt->execute([$user_id]);$hash=$stmt->fetchColumn();
    $email=trim((string)($_POST['new_email']??''));
    if (!$hash || !password_verify((string)($_POST['email_password']??''),$hash)) $errors['email']='Password is incorrect.';
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors['email']='Invalid e-mail address.';
    else {$pdo->prepare('UPDATE users SET email=? WHERE id=?')->execute([$email,$user_id]);$user['email']=$email;$success['email']='E-Mail updated successfully.';}
}
if (isset($_POST['deleteAccount'])) {
    $stmt=$pdo->prepare('SELECT password FROM users WHERE id=?');$stmt->execute([$user_id]);$hash=$stmt->fetchColumn();
    if (!$hash || !password_verify((string)($_POST['delete_password']??''),$hash)) $errors['delete']='Password is incorrect.';
    else {$pdo->prepare('DELETE FROM users WHERE id=?')->execute([$user_id]);session_destroy();header('Location: index.php');exit;}
}

// =========================================================

// LOAD REGISTERED BOXES

// =========================================================

$stmt = $pdo->prepare("SELECT box_id FROM user_boxes WHERE user_id = ? ORDER BY registered_at DESC");

$stmt->execute([$user_id]);

$registeredBoxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Active remote permissions granted by the current user, grouped by box.
$remoteAccessByBox = [];
$stmt = $pdo->prepare("SELECT bp.box_id, bp.user_id, u.username, bp.created_at
    FROM box_permissions bp
    JOIN user_boxes ub ON ub.box_id = bp.box_id AND ub.user_id = ?
    JOIN users u ON u.id = bp.user_id
    WHERE bp.revoked_at IS NULL
    ORDER BY u.username ASC");
$stmt->execute([$user_id]);
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $permission) {
    $remoteAccessByBox[$permission['box_id']][] = $permission;
}

// Boxes shared with this user. Never treat these as owned boxes.
$stmt = $pdo->prepare("SELECT ub.box_id, owner.username AS owner_username, bp.created_at AS shared_at
    FROM box_permissions bp
    JOIN user_boxes ub ON ub.box_id = bp.box_id
    JOIN users owner ON owner.id = ub.user_id
    WHERE bp.user_id = ? AND bp.revoked_at IS NULL AND ub.user_id <> ?
    ORDER BY bp.created_at DESC");
$stmt->execute([$user_id, $user_id]);
$sharedWithMe = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =========================================================

// LOAD BOX HISTORY

// =========================================================

$boxHistory = [];

if (!empty($registeredBoxes) || !empty($sharedWithMe)) {

    $boxIds = array_values(array_unique(array_merge(array_column($registeredBoxes, 'box_id'), array_column($sharedWithMe, 'box_id'))));

    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));

    $stmt = $pdo->prepare("

        SELECT box_id, lock_status, open_time, created_at, protection_level_timer, protection_level_password

        FROM box_data_history

        WHERE box_id IN ($placeholders)

        ORDER BY created_at DESC

    ");

    $stmt->execute($boxIds);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {

        $boxHistory[$row['box_id']][] = $row;

    }

}

// =========================================================

// LOAD PER-BOX DETAILS

// =========================================================

$boxDetails = [];

if (!empty($registeredBoxes) || !empty($sharedWithMe)) {

    $boxIds = array_values(array_unique(array_merge(array_column($registeredBoxes, 'box_id'), array_column($sharedWithMe, 'box_id'))));

    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));

    $stmt = $pdo->prepare("

        SELECT box_id, keyholder AS keyholder, box_content FROM box_details

        WHERE box_id IN ($placeholders)

    ");

    $stmt->execute($boxIds);

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $d) {

        $boxDetails[$d['box_id']] = $d;

    }

}

// =========================================================

// EFFECTIVE LOCK STATE

// (a box with lock_status = 1 counts as open once its timer

//  has expired, even if no new history row has arrived yet)

// =========================================================

function is_effectively_locked(?array $status): bool

{

    if ($status === null || (int)$status['lock_status'] !== 1) {

        return false;

    }

    if (!empty($status['protection_level_timer']) && !empty($status['open_time'])) {

        $openTime = strtotime($status['open_time']);

        if ($openTime !== false && $openTime <= time()) {

            return false; // timer expired - box is effectively open

        }

    }

    return true;

}

// =========================================================

// DERIVE CURRENT STATUS

// =========================================================

$boxActual = [];

foreach ($boxHistory as $boxName => $entries) {

    if (empty($entries)) continue;

    $boxActual[$boxName] = [

        'lock_status'               => $entries[0]['lock_status'],

        'locked_since'              => $entries[0]['created_at'],

        'open_time'                 => $entries[0]['open_time'],

        'protection_level_timer'    => $entries[0]['protection_level_timer'],

        'protection_level_password' => $entries[0]['protection_level_password'],

        'effectively_locked'        => is_effectively_locked($entries[0]),

    ];

}

// Current status for boxes shared with this user (the device's actual status).
foreach ($sharedWithMe as $shared) {
    $sharedId = (int)$shared['box_id'];
    $stmt = $pdo->prepare('SELECT lock_status, locked_since, open_time, protection_level_timer, protection_level_password FROM box_data_actual WHERE box_id = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$sharedId]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($current) {
        $boxActual[$sharedId] = $current;
        $boxActual[$sharedId]['effectively_locked'] = is_effectively_locked($current);
    }
}

// =========================================================

// LOCKED DURATION

// =========================================================

function locked_duration(?string $lockedSince): string

{

    if (!$lockedSince) return '';

    $diff = max(0, time() - strtotime($lockedSince));

    $days    = intdiv($diff, 86400);

    $hours   = intdiv($diff % 86400, 3600);

    $minutes = intdiv($diff % 3600, 60);

    $parts = [];

    if ($days > 0)  $parts[] = $days . 'd';

    if ($hours > 0) $parts[] = $hours . 'h';

    if ($minutes > 0 || empty($parts)) $parts[] = $minutes . 'm';

    return implode(' ', $parts);

}

?>
