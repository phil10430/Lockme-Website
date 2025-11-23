<?php

require_once __DIR__ . '/includes/config.php';

session_start();

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Prüfen, ob der Token existiert und der User noch nicht verifiziert ist
    $stmt = $pdo->prepare("SELECT id FROM users WHERE verification_token = ? AND email_verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // User als verifiziert markieren
        $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Erfolgsnachricht in die Session schreiben
        $_SESSION['flash_message'] = "✅ Email successfully verified! You can now log in.";

        // Direkt weiterleiten zur Startseite
        header("Location: index.php");
        exit;
    } else {
        // Token ungültig oder bereits genutzt
        $_SESSION['flash_message'] = "❌ Invalid or expired verification link.";

        // Auch hier zurück zu index.php leiten
        header("Location: index.php");
        exit;
    }
} else {
    // Kein Token übergeben → einfach zurückleiten
    $_SESSION['flash_message'] = "❌ No verification token provided.";
    header("Location: index.php");
    exit;
}
?>
