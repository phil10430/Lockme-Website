<?php

session_start();

require_once __DIR__ . '/includes/config.php';

$username = $password = "";
$username_err = $password_err = $login_err = "";

// Form wurde abgeschickt?
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submitLogin'])) {

    // Eingaben prüfen
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Wenn keine Fehler vorhanden sind
    if (empty($username_err) && empty($password_err)) {

        try {
            // Benutzer anhand des Namens abrufen
            $stmt = $pdo->prepare("SELECT id, username, password, email_verified FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Prüfen, ob E-Mail verifiziert ist
                if (!$user['email_verified']) {
                    $login_err = "Please verify your email before logging in.";
                } elseif (password_verify($password, $user['password'])) {
                    // Passwort korrekt → Login erfolgreich
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $user["id"];
                    $_SESSION["username"] = $user["username"];

                    // Optional: Loginstatus in DB setzen
                    $update = $pdo->prepare("UPDATE users SET appLoggedIn = 1 WHERE id = :id");
                    $update->execute([':id' => $user['id']]);

                    header("Location: index.php");
                    exit;
                } else {
                    $login_err = "Invalid password.";
                }
            } else {
                $login_err = "Invalid username.";
            }
        } catch (PDOException $e) {
            // Fehlermeldung NICHT im Browser ausgeben (nur beim Debuggen)
            $login_err = "Database error. Please try again later.";
        }
    }
}
?>
