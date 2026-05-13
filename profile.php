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

// User-Daten laden
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Passwort ändern
if (isset($_POST['changePassword'])) {
    $current_pw = $_POST['current_password'];
    $new_pw     = $_POST['new_password'];
    $confirm_pw = $_POST['confirm_new_password'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    if (!password_verify($current_pw, $row['password'])) {
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

// E-Mail ändern
if (isset($_POST['changeEmail'])) {
    $new_email  = trim($_POST['new_email']);
    $current_pw = $_POST['email_password'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    if (!password_verify($current_pw, $row['password'])) {
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

// Account löschen
if (isset($_POST['deleteAccount'])) {
    $current_pw = $_POST['delete_password'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    if (!password_verify($current_pw, $row['password'])) {
        $errors['delete'] = "Password is incorrect.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        session_destroy();
        header("location: index.php");
        exit;
    }
}
?>