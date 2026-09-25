<?php
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: index.php");
    exit;
}

$user_id  = $_SESSION["id"];
$username = $_SESSION["username"];
$errors   = [];
$success  = [];

// Remove box (AJAX handler - must run before any HTML output)
if (isset($_POST['removeBox'])) {
    $boxId = trim($_POST["boxId"]);

    header('Content-Type: application/json');

    if (!preg_match('/^\d{6}$/', $boxId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid box ID.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM user_boxes WHERE user_id = :user_id AND box_id = :box_id");
        $stmt->execute([
            ':user_id' => $user_id,
            ':box_id'  => $boxId,
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Box removed.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Box not found.']);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An error occurred.']);
    }
    exit;
}

// Save box details (AJAX handler - must run before any HTML output)
if (isset($_POST['saveBoxDetails'])) {
    header('Content-Type: application/json');

    $boxId      = trim($_POST['boxId'] ?? '');
    $nameTop    = trim($_POST['name_top'] ?? '');
    $nameSub    = trim($_POST['name_sub'] ?? '');
    $boxContent = trim($_POST['box_content'] ?? '');
    $targetDate = trim($_POST['target_open_date'] ?? '');

    if (!preg_match('/^\d+$/', $boxId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid box ID.']);
        exit;
    }

    if ($nameTop === '') {
        echo json_encode(['success' => false, 'message' => 'Name is required.']);
        exit;
    }

    // Ownership check: this box must actually belong to the logged-in user
    $stmt = $pdo->prepare("SELECT 1 FROM user_boxes WHERE user_id = ? AND box_id = ?");
    $stmt->execute([$user_id, $boxId]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Box not found.']);
        exit;
    }

    // datetime-local sends "YYYY-MM-DDTHH:MM" - normalise or nullify
    $targetDateSql = null;
    if ($targetDate !== '') {
        $ts = strtotime($targetDate);
        if ($ts === false) {
            echo json_encode(['success' => false, 'message' => 'Invalid date.']);
            exit;
        }
        $targetDateSql = date('Y-m-d H:i:s', $ts);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO user_details (user_id, box_id, name_top, name_sub, box_content, target_open_date)
            VALUES (:user_id, :box_id, :name_top, :name_sub, :box_content, :target_open_date)
            ON DUPLICATE KEY UPDATE
                name_top = VALUES(name_top),
                name_sub = VALUES(name_sub),
                box_content = VALUES(box_content),
                target_open_date = VALUES(target_open_date)
        ");
        $stmt->execute([
            ':user_id'          => $user_id,
            ':box_id'           => $boxId,
            ':name_top'         => $nameTop,
            ':name_sub'         => $nameSub ?: null,
            ':box_content'      => $boxContent ?: null,
            ':target_open_date' => $targetDateSql,
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An error occurred.']);
    }
    exit;
}


// Toggle public status (AJAX handler - must run before any HTML output)
if (isset($_POST['togglePublicStatus'])) {
    header('Content-Type: application/json');

    $newStatus = (isset($_POST['public_status']) && $_POST['public_status'] === '1') ? 1 : 0;

    try {
        $stmt = $pdo->prepare("UPDATE users SET public_status = :status WHERE id = :user_id");
        $stmt->execute([
            ':status'  => $newStatus,
            ':user_id' => $user_id,
        ]);

        echo json_encode(['success' => true, 'public_status' => $newStatus]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An error occurred.']);
    }
    exit;
}


// Load user data
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Change password
if (isset($_POST['changePassword'])) {
    $current_pw = $_POST['current_password'];
    $new_pw     = $_POST['new_password'];
    $confirm_pw = $_POST['confirm_new_password'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current_pw, $row['password'])) {
        $errors['password'] = "Current password is incorrect.";
    } elseif (strlen($new_pw) < 6) {
        $errors['password'] = "New password must be at least 6 characters.";
    } elseif ($new_pw !== $confirm_pw) {
        $errors['password'] = "Passwords do not match.";
    } else {
        $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $user_id]);
        $success['password'] = "Password updated successfully.";
    }
}

// Change email
if (isset($_POST['changeEmail'])) {
    $new_email  = trim($_POST['new_email']);
    $current_pw = $_POST['email_password'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current_pw, $row['password'])) {
        $errors['email'] = "Password is incorrect.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid e-mail address.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$new_email, $user_id]);
        $user['email'] = $new_email;
        $success['email'] = "E-Mail updated successfully.";
    }
}

// Delete account
if (isset($_POST['deleteAccount'])) {
    $current_pw = $_POST['delete_password'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current_pw, $row['password'])) {
        $errors['delete'] = "Password is incorrect.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        session_destroy();
        header("location: index.php");
        exit;
    }
}

// Load registered boxes
$stmt = $pdo->prepare("SELECT box_id FROM user_boxes WHERE user_id = ? ORDER BY registered_at DESC");
$stmt->execute([$user_id]);
$registeredBoxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load box history for all registered boxes
$boxHistory = [];

if (!empty($registeredBoxes)) {
    $boxIds = array_column($registeredBoxes, 'box_id');
    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));

    $stmt = $pdo->prepare("
        SELECT box_name, lock_status, open_time, created_at, 
               protection_level_timer, protection_level_password
        FROM box_data_history
        WHERE box_name IN ($placeholders)
        ORDER BY created_at DESC
    ");
    $stmt->execute($boxIds);
    $historyRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group by box_name
    foreach ($historyRows as $row) {
        $boxHistory[$row['box_name']][] = $row;
    }
}

// Load per-box details (name_top, name_sub, box_content, target_open_date)
$boxDetails = [];

if (!empty($registeredBoxes)) {
    $boxIds = array_column($registeredBoxes, 'box_id');
    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));

    $stmt = $pdo->prepare("
        SELECT box_id, name_top, name_sub, box_content, target_open_date
        FROM user_details
        WHERE box_id IN ($placeholders)
    ");
    $stmt->execute($boxIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $d) {
        $boxDetails[$d['box_id']] = $d;
    }
}
// Load per-box details (name_top, name_sub, box_content, target_open_date)
$boxDetails = [];

if (!empty($registeredBoxes)) {
    $boxIds = array_column($registeredBoxes, 'box_id');
    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));

    $stmt = $pdo->prepare("
        SELECT box_id, name_top, name_sub, box_content, target_open_date
        FROM user_details
        WHERE box_id IN ($placeholders)
    ");
    $stmt->execute($boxIds);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $d) {
        $boxDetails[$d['box_id']] = $d;
    }
}

// Derive current status (incl. locked_since) from the newest box_data_history entry per box.
// $boxHistory is already ordered DESC by created_at, so index [0] per box is the latest.
$boxActual = [];

foreach ($boxHistory as $boxName => $entries) {
    $boxActual[$boxName] = [
        'lock_status'  => $entries[0]['lock_status'],
        'locked_since' => $entries[0]['created_at'],
    ];
}

function locked_duration(?string $lockedSince): string
{
    if (!$lockedSince) {
        return '';
    }

    $diff = time() - strtotime($lockedSince);
    if ($diff < 0) {
        $diff = 0;
    }

    $days    = intdiv($diff, 86400);
    $hours   = intdiv($diff % 86400, 3600);
    $minutes = intdiv($diff % 3600, 60);

    $parts = [];
    if ($days > 0)    $parts[] = $days . 'd';
    if ($hours > 0)   $parts[] = $hours . 'h';
    if ($minutes > 0 || empty($parts)) $parts[] = $minutes . 'm';

    return implode(' ', $parts);
}
?>