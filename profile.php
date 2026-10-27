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

if (isset($_POST['togglePublicStatus'])) {
    $visibility = ($_POST['public_status'] ?? '') === '1' ? 'public' : 'private';
    if (!in_array((string)($_POST['public_status'] ?? ''), ['0', '1'], true)) {
        profileJson(['success' => false, 'message' => 'Invalid visibility value.'], 400);
    }
    try {
        $stmt = $pdo->prepare('INSERT INTO user_details (user_id,profile_visibility) VALUES (?,?) ON DUPLICATE KEY UPDATE profile_visibility=VALUES(profile_visibility)');
        $stmt->execute([$user_id, $visibility]);
        profileJson(['success' => true, 'profile_visibility' => $visibility]);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        profileJson(['success' => false, 'message' => 'Could not update visibility.'], 500);
    }
}

if (isset($_POST['saveUserDetails'])) {
    $newUsername=trim((string)($_POST['username']??''));
    $birth=trim((string)($_POST['birth_date']??''));
    $visibility=(string)($_POST['profile_visibility']??'private');
    $socialX=trim((string)($_POST['social_x']??''));
    $socialI=trim((string)($_POST['social_instagram']??''));
    $socialB=trim((string)($_POST['social_bluesky']??''));
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/D',$newUsername)) profileJson(['success'=>false,'message'=>'Username must contain 3–20 letters, numbers or underscores.'],400);

    $reservedUsernames = [
        'admin',
        'administrator',
        'support',
        'moderator',
        'lockmebox',
        'official',
        'system',
        'security',
        'root'
    ];

    if (in_array(strtolower($newUsername), $reservedUsernames, true)) {
        profileJson([
            'success' => false,
            'message' => 'This username is reserved.'
        ], 400);
    }

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
        // Aktuellen Benutzernamen und letzte Änderung laden
        $stmt = $pdo->prepare('
            SELECT username, username_changed_at
            FROM users
            WHERE id = ?
            FOR UPDATE
        ');

        $stmt->execute([$user_id]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentUser) {
            throw new RuntimeException('User not found.');
        }

        // Nur prüfen, wenn sich der Benutzername tatsächlich ändert
        if ($newUsername !== $currentUser['username']) {

            // Prüfen, ob der Benutzername in den letzten 30 Tagen geändert wurde
            if (!empty($currentUser['username_changed_at'])) {

                $lastChange = new DateTimeImmutable(
                    $currentUser['username_changed_at']
                );

                $nextChange = $lastChange->modify('+30 days');

                if (new DateTimeImmutable() < $nextChange) {
                    throw new RuntimeException(
                        'You can change your username again on '
                        . $nextChange->format('d.m.Y')
                        . '.'
                    );
                }
            }

            // Benutzernamen ändern und Zeitpunkt speichern
            $stmt = $pdo->prepare('
                UPDATE users
                SET
                    username = ?,
                    username_changed_at = NOW()
                WHERE id = ?
            ');

            $stmt->execute([$newUsername, $user_id]);
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

$stmt=$pdo->prepare('SELECT u.username,u.email,u.created_at,d.avatar_path,d.social_x,d.social_instagram,d.social_bluesky,d.birth_date,d.relationship_status,d.profile_visibility FROM users u LEFT JOIN user_details d ON d.user_id=u.id WHERE u.id=?');
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

// =========================================================

// LOAD BOX HISTORY

// =========================================================

$boxHistory = [];

if (!empty($registeredBoxes)) {

    $boxIds = array_column($registeredBoxes, 'box_id');

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

if (!empty($registeredBoxes)) {

    $boxIds = array_column($registeredBoxes, 'box_id');

    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));

    $stmt = $pdo->prepare("

        SELECT box_id, keyholder, box_content FROM box_details

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
