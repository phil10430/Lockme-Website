<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helper_functions.php';

$username = $email = $password = $confirm_password = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 🔹 username prüfen
    $username = trim($_POST["username"] ?? '');
    if (empty($username)) {
        $username_err['username'] = "Please enter a username.";
    } elseif (!isValidusername($username)) {
        $errors['username'] = "username can only contain letters and numbers.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors['username'] = "This username is already taken.";
        }
    }

    // 🔹 Email prüfen
    $email = trim($_POST["email"] ?? '');
    if (empty($email)) {
        $errors['email'] = "Please enter an email.";
    } elseif (!isValidEmail($email)) {
        $errors['email'] = "Invalid email format.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = "This email is already taken.";
        }
    }

    // 🔹 Passwort prüfen
    $password = trim($_POST["password"] ?? '');
    $confirm_password = trim($_POST["confirm_password"] ?? '');
    if (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }
    if (!isset($_POST['accept_terms'])) {
        $errors['terms'] = 'Please accept the Terms of Service.';
    }

    // ✅ Wenn keine Fehler → neuen User anlegen
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32)); // eindeutiger Bestätigungs-Token

        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, email_verified, verification_token)
            VALUES (?, ?, ?, 0, ?)
        ");
        $stmt->execute([$username, $email, $hash, $token]);

        // 🔹 E-Mail mit Bestätigungslink senden
        $verify_link = "https://lockmebox.com/verify_email.php?token=$token";
        $subject = "Confirm your registration";
        $message = "Hello $username,\n\nPlease verify your email by clicking this link:\n$verify_link";
        $headers = "From: noreply@lockmebox.com\r\n";

       if (mail($email, $subject, $message, $headers)) {
        $_SESSION['flash_message'] = "Registration successful! Please check your email.";
        header("Location: index.php");
        exit;
       }

    }
}
?>
