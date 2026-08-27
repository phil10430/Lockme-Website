<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helper_functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

$username = $email = $password = $confirm_password = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 🔹 Username prüfen
    $username = trim($_POST["username"] ?? '');

    if (empty($username)) {
        $errors['username'] = "Please enter a username.";
    } elseif (!isValidusername($username)) {
        $errors['username'] = "Username can only contain letters and numbers.";
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

    if (empty($password)) {
        $errors['password'] = "Please enter a password.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    // 🔹 Terms prüfen
    if (!isset($_POST['accept_terms'])) {
        $errors['terms'] = 'Please accept the Terms of Service.';
    }

    // ✅ Wenn keine Fehler → User anlegen
    if (empty($errors)) {

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare("
            INSERT INTO users (
                username,
                email,
                password,
                email_verified,
                verification_token
            )
            VALUES (?, ?, ?, 0, ?)
        ");

        $stmt->execute([
            $username,
            $email,
            $hash,
            $token
        ]);

        // 🔗 Bestätigungslink
        $verify_link = "https://lockmebox.com/verify_email.php?token=" . urlencode($token);

        // 👤 Sicherer Username für HTML-Mail
        $username_safe = htmlspecialchars(
            $username,
            ENT_QUOTES,
            'UTF-8'
        );

        // 📧 E-Mail Template laden
        ob_start();

        include __DIR__ . "/templates/email_verify.php";

        $message = ob_get_clean();

        $subject = "Confirm your registration";

        // 📬 SMTP über PHPMailer
        try {

            $mail = new PHPMailer(true);

            // SMTP
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;

            // SSL / Port 465
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // Zeichensatz
            $mail->CharSet = 'UTF-8';

            // Absender
            $mail->setFrom(
                'noreply@lockmebox.com',
                'LockMeBox'
            );

            // Empfänger
            $mail->addAddress(
                $email,
                $username
            );

            // Antwortadresse
            $mail->addReplyTo(
                'support@lockmebox.com',
                'LockMeBox Support'
            );

            // HTML-Mail
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            // Fallback für Mailprogramme ohne HTML
            $mail->AltBody =
                "Please confirm your registration:\n\n" .
                $verify_link;

            // Senden
            $mail->send();

            // ✅ Erfolg
            $_SESSION['flash_message'] =
                "Registration successful! Please check your email.";

            header("Location: control_center.php");
            exit;

        } catch (PHPMailerException $e) {

            // Fehler ins Server-Log
            error_log(
                "Registration email failed for {$email}: " .
                $mail->ErrorInfo
            );

            // Fehlermeldung für den Benutzer
            $errors['email'] =
                "Your account was created, but we could not send the verification email. Please contact support.";
        }
    }
}
?>