<?php
//Logout
// Include config file
require_once "config.php";
include 'helper_functions.php';
// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Eingaben trimmen (kein mysqli_real_escape_string mehr nötig)
    $username = trim($_POST["username"] ?? '');
    $isLoggedIn = 0;

    try {
        $stmt = $pdo->prepare("UPDATE users SET app_logged_in = :status WHERE username = :username");
        $stmt->execute([
            ':status'   => $isLoggedIn,
            ':username' => $username
        ]);

        // Optional: prüfen, ob tatsächlich etwas geändert wurde
        if ($stmt->rowCount() > 0) {
            echo "logged_out";
        } else {
            echo "no_change"; // z. B. wenn User nicht gefunden oder bereits ausgeloggt
        }
    } catch (PDOException $e) {
        // Niemals SQL-Fehler direkt an User ausgeben
        error_log("Logout-DB-Fehler: " . $e->getMessage());
        echo "error";
    }
}
?>