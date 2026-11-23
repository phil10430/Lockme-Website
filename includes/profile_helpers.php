<?php

function profileSocialValid(string $url, array $hosts): bool
{
    if ($url === '') return true;
    if (strlen($url) > 255 || !filter_var($url, FILTER_VALIDATE_URL)) return false;
    $p = parse_url($url);
    return is_array($p) && strtolower($p['scheme'] ?? '') === 'https'
        && in_array(strtolower($p['host'] ?? ''), $hosts, true)
        && !isset($p['user'], $p['pass']) && !isset($p['port'])
        && !isset($p['query']) && !isset($p['fragment']);
}

function profileJson(array $result, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result);
    exit;
}

function profileAvatarUpload(int $userId): ?string
{
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES['avatar'];
    if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 2 * 1024 * 1024 || !is_uploaded_file($f['tmp_name']))
        throw new RuntimeException('Avatar upload failed or exceeds 2 MB.');
    $mime  = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime]) || @getimagesize($f['tmp_name']) === false)
        throw new RuntimeException('Only valid JPG, PNG and WebP images are allowed.');
    $dir = dirname(__DIR__) . '/uploads/user_avatars/';
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir))
        throw new RuntimeException('Avatar directory could not be created.');
    $name = bin2hex(random_bytes(16)) . '.' . $types[$mime];
    if (!move_uploaded_file($f['tmp_name'], $dir . $name)) throw new RuntimeException('Could not save avatar.');
    return 'uploads/user_avatars/' . $name;
}

function profileDeleteOwnedAvatar(?string $path): void
{
    if (!$path || !str_starts_with($path, 'uploads/user_avatars/')) return;
    $name = basename($path);
    if ($path !== 'uploads/user_avatars/' . $name) return;
    $file = dirname(__DIR__) . '/uploads/user_avatars/' . $name;
    if (is_file($file)) @unlink($file);
}

/** A box with lock_status = 1 counts as open once its timer has expired. */
function is_effectively_locked(?array $status): bool
{
    if ($status === null || (int)$status['lock_status'] !== 1) return false;
    if (!empty($status['protection_level_timer']) && !empty($status['open_time'])) {
        $openTime = strtotime($status['open_time']);
        if ($openTime !== false && $openTime <= time()) return false;
    }
    return true;
}

function locked_duration(?string $lockedSince): string
{
    if (!$lockedSince) return '';
    $diff    = max(0, time() - strtotime($lockedSince));
    $days    = intdiv($diff, 86400);
    $hours   = intdiv($diff % 86400, 3600);
    $minutes = intdiv($diff % 3600, 60);
    $parts = [];
    if ($days > 0)  $parts[] = $days . 'd';
    if ($hours > 0) $parts[] = $hours . 'h';
    if ($minutes > 0 || empty($parts)) $parts[] = $minutes . 'm';
    return implode(' ', $parts);
}

/**
 * Loads everything the profile page needs about boxes.
 * Returns: registeredBoxes, remoteAccessByBox, sharedWithMe, boxHistory, boxDetails, boxActual
 */
function profileLoadBoxData(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT box_id FROM user_boxes WHERE user_id = ? ORDER BY registered_at DESC');
    $stmt->execute([$userId]);
    $registeredBoxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Active remote permissions granted by this user, grouped by box
    $remoteAccessByBox = [];
    $stmt = $pdo->prepare('SELECT bp.box_id, bp.user_id, u.username, bp.created_at
        FROM box_permissions bp
        JOIN user_boxes ub ON ub.box_id = bp.box_id AND ub.user_id = ?
        JOIN users u ON u.id = bp.user_id
        WHERE bp.revoked_at IS NULL
        ORDER BY u.username ASC');
    $stmt->execute([$userId]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $perm) {
        $remoteAccessByBox[$perm['box_id']][] = $perm;
    }

    // Boxes shared with this user
    $stmt = $pdo->prepare('SELECT ub.box_id, owner.username AS owner_username, bp.created_at AS shared_at
        FROM box_permissions bp
        JOIN user_boxes ub ON ub.box_id = bp.box_id
        JOIN users owner ON owner.id = ub.user_id
        WHERE bp.user_id = ? AND bp.revoked_at IS NULL AND ub.user_id <> ?
        ORDER BY bp.created_at DESC');
    $stmt->execute([$userId, $userId]);
    $sharedWithMe = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $boxHistory = [];
    $boxDetails = [];
    $boxIds = array_values(array_unique(array_merge(
        array_column($registeredBoxes, 'box_id'),
        array_column($sharedWithMe, 'box_id')
    )));

    if ($boxIds) {
        $ph = implode(',', array_fill(0, count($boxIds), '?'));

        $stmt = $pdo->prepare("SELECT box_id, lock_status, open_time, created_at, protection_level_timer, protection_level_password
                               FROM box_data_history WHERE box_id IN ($ph) ORDER BY created_at DESC");
        $stmt->execute($boxIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $boxHistory[$row['box_id']][] = $row;
        }

        $stmt = $pdo->prepare("SELECT box_id, keyholder, box_content FROM box_details WHERE box_id IN ($ph)");
        $stmt->execute($boxIds);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $d) {
            $boxDetails[$d['box_id']] = $d;
        }
    }

    // Current status of own boxes (derived from history)
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

    // Current status of shared boxes (actual device status)
    $stmt = $pdo->prepare('SELECT lock_status, locked_since, open_time, protection_level_timer, protection_level_password
                           FROM box_data_actual WHERE box_id = ? ORDER BY id DESC LIMIT 1');
    foreach ($sharedWithMe as $shared) {
        $sharedId = (int)$shared['box_id'];
        $stmt->execute([$sharedId]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($current) {
            $boxActual[$sharedId] = $current + ['effectively_locked' => is_effectively_locked($current)];
        }
    }

    return compact('registeredBoxes', 'remoteAccessByBox', 'sharedWithMe', 'boxHistory', 'boxDetails', 'boxActual');
}

function profileAge(?string $birthDate): ?int
{
    if (!$birthDate) return null;
    try {
        $age = (new DateTimeImmutable($birthDate))->diff(new DateTimeImmutable('today'))->y;
        return ($age >= 18 && $age <= 120) ? $age : null;
    } catch (Exception $e) {
        return null;
    }
}