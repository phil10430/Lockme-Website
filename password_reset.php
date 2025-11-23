<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helper_functions.php';

$hide = 0;
$error = [];
$success = "";

$token = $_GET['token'] ?? '';

if ($token == '') {
    $error[] = "Invalid token.";
    $hide = 1;
}

// Prüfen, ob Token existiert und nicht abgelaufen
$sql = "SELECT email FROM pass_reset WHERE token = :token AND expires_at > NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute([':token' => $token]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $error[] = "Link is invalid or has expired.";
    $hide = 1;
} else {
    $email = $row['email'];
}

// Passwort setzen
if (isset($_POST['sub_set'])) {

    $password = trim($_POST["password"]);
    $passwordConfirm = trim($_POST["passwordConfirm"]);

    if ($password == '') $error[] = 'Please enter a password.';
    if ($passwordConfirm == '') $error[] = 'Please confirm password.';
    if ($password != $passwordConfirm) $error[] = 'Passwords do not match.';
    if (strlen($password) < 6) $error[] = 'Password must have at least 6 characters.';

    if (empty($error)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Passwort updaten
        $sql = "UPDATE users SET password = :password WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':password' => $passwordHash,
            ':email' => $email
        ]);

        // Token löschen, damit er nur einmal verwendet werden kann
        $sql = "DELETE FROM pass_reset WHERE token = :token";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':token' => $token]);

        $success = "<div class='alert alert-success'>Your password has been updated successfully. Login <a href='index.php'>here</a>.</div>";
        $hide = 1;
    }
}
?>
