<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helper_functions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $login_email = test_input($_POST["login_var"]);
    $username_input = test_input($_POST["username"] ?? ''); // optional Username

    // neutrale Flash-Meldung immer gleich
    $flash_msg = "✔️ If the email exists, a reset link has been sent.";

    // Alte Tokens löschen (abgelaufen)
    $sql = "DELETE FROM pass_reset WHERE expires_at <= NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Benutzer prüfen (E-Mail + optional Username)
    $sql = "SELECT * FROM users WHERE email = :email";
    $params = [':email' => $login_email];

    if (!empty($username_input)) {
        $sql .= " AND username = :username";
        $params[':username'] = $username_input;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Token generieren
        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Token in DB speichern
        $sql = "INSERT INTO pass_reset (email, token, expires_at) VALUES (:email, :token, :expires)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':email' => $user['email'],
            ':token' => $token,
            ':expires' => $expires
        ]);

        // Mail senden
        $FromName  = "lockmebox.com";
        $FromEmail = "noreply@lockmebox.com";
        $ReplyTo   = "noreply@lockmebox.com";

        $headers  = "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=utf-8\n";
        $headers .= "From: $FromName <$FromEmail>\n";
        $headers .= "Reply-To: $ReplyTo\n";

        $subject = "Your password reset link";
        $mlink   = "https://lockmebox.com/password_reset_page.php?token=$token";

        $msg = "
            <html>
            <body>
                Reset your password with this <a href='$mlink'>link</a>.
            </body>
            </html>
        ";

        @mail($user['email'], $subject, $msg, $headers, '-f'.$FromEmail);
    }

    // Immer gleiche Meldung für Sicherheit
    $_SESSION['flash_message'] = $flash_msg;
    header("Location: forgot_password_page.php");
    exit;
}
