
<?php
// ============================================================
// config.php — zentrale Konfiguration für DB und Fehlerbehandlung
// ============================================================

// 🔹 Datenbankverbindungsdaten
$db_host = 'localhost:3306';      // oder IP-Adresse deines DB-Servers
$db_name = 'web688s4_lockmebox'; // Name deiner Datenbank
$db_user = 'web688s4_lockmebox';       // DB-Benutzername
$db_pass = 'Q8O~mA*)#2J-l6yxc+MITvC[k!`?{';   // DB-Passwort

// 🔹 Optionale Einstellungen (Fehleranzeige, Zeichensatz)
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Fehler als Exception werfen
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Ergebnisse als assoziatives Array
    PDO::ATTR_EMULATE_PREPARES => false, // native prepared statements (sicherer)
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4" // UTF-8-Unterstützung
];

// 🔹 Verbindung aufbauen
try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        $options
    );
} catch (PDOException $e) {
    // Benutzerfreundliche Fehlermeldung (keine sensiblen Infos!)
    die("Database connection failed. Please try again later.");
    // oder: error_log($e->getMessage()); // Für Logs
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>
