<?php
// Include config file
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helper_functions.php';

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Eingaben holen & trimmen
    $username = trim($_POST["username"] ?? '');
    $password = trim($_POST["password"] ?? '');

    // Validierung
    if (empty($username) || empty($password)) {
        echo "username or password is empty.";
        exit;
    }

    try {
        // Nutzer abrufen
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Passwort prüfen
            if (password_verify($password, $user['password'])) {
                // Loginstatus updaten
                $stmt = $pdo->prepare("UPDATE users SET app_logged_in = 1 WHERE username = :username");
                $stmt->execute([':username' => $username]);

                // Session setzen (optional)
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $user["id"];
                $_SESSION["username"] = $user["username"];

                echo "logged_in"; // Android: public void onResponse(String response)
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "Username doesn't exist.";
        }
    } catch (PDOException $e) {
        // Bei Fehlern keine SQL-Daten ausgeben!
        echo "Database error. Please try again later.";
        // Für Debugging (nur lokal!): echo $e->getMessage();
    }
}

?>