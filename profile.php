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


?>