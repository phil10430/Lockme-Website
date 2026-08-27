<?php

session_start();

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helper_functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $login_email = test_input($_POST["login_var"] ?? '');
    $username_input = test_input($_POST["username"] ?? '');

    // Neutrale Meldung immer gleich
    $flash_msg = "✔️ If the email exists, a reset link has been sent.";

    // Abgelaufene Tokens löschen
    $sql = "DELETE FROM pass_reset WHERE expires_at <= NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Benutzer prüfen
    $sql = "SELECT * FROM users WHERE email = :email";
    $params = [
        ':email' => $login_email
    ];

    if (!empty($username_input)) {
        $sql .= " AND username = :username";
        $params[':username'] = $username_input;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        // 🔐 Token generieren
        $token = bin2hex(random_bytes(50));

        // ⏰ 1 Stunde gültig
        $expires = date(
            "Y-m-d H:i:s",
            strtotime("+1 hour")
        );

        // Alten Reset-Token für diesen Benutzer löschen
        $stmt = $pdo->prepare(
            "DELETE FROM pass_reset WHERE email = :email"
        );

        $stmt->execute([
            ':email' => $user['email']
        ]);

        // Token speichern
        $sql = "
            INSERT INTO pass_reset (
                email,
                token,
                expires_at
            )
            VALUES (
                :email,
                :token,
                :expires
            )
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':email'   => $user['email'],
            ':token'   => $token,
            ':expires' => $expires
        ]);

        // 🔗 Reset-Link
        $mlink =
            "https://lockmebox.com/password_reset_page.php?token=" .
            urlencode($token);

        // 👤 Username für HTML-Mail absichern
        $username_safe = htmlspecialchars(
            $user['username'],
            ENT_QUOTES,
            'UTF-8'
        );

        // 📧 E-Mail Template laden
        ob_start();

        include __DIR__ . "/templates/email_forgot_password.php";

        $msg = ob_get_clean();

        // 📬 SMTP-Mail senden
        try {

            $mail = new PHPMailer(true);

            // SMTP
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;

            // Hostinger SMTP SSL
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
                $user['email'],
                $user['username']
            );

            // Reply-To
            $mail->addReplyTo(
                'support@lockmebox.com',
                'LockMeBox Support'
            );

            // HTML-Mail
            $mail->isHTML(true);

            $mail->Subject = "Reset your password";

            $mail->Body = $msg;

            // Text-Version
            $mail->AltBody =
                "You requested a password reset for your LockMeBox account.\n\n" .
                "Please use the following link to reset your password:\n\n" .
                $mlink . "\n\n" .
                "This link will expire in 1 hour.";

            // Mail senden
            $mail->send();

        } catch (PHPMailerException $e) {

            // SMTP-Fehler nur ins Server-Log
            error_log(
                "Password reset email failed for {$user['email']}: " .
                $mail->ErrorInfo
            );

            // Dem Benutzer KEINE SMTP-Details zeigen
        }
    }

    // 🔒 Immer gleiche Meldung
    $_SESSION['flash_message'] = $flash_msg;

    header("Location: forgot_password_page.php");
    exit;
}