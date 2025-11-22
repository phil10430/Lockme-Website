
<?php
// ============================================================
// config.php — zentrale Konfiguration für DB und Fehlerbehandlung
// ============================================================

// 🔹 Datenbankverbindungsdaten
$db_host = 'localhost';      // oder IP-Adresse deines DB-Servers
$db_name = 'u456104939_lockmebox'; // Name deiner Datenbank
$db_user = 'u456104939_phil';       // DB-Benutzername
$db_pass = 'mq<cI(E5RRnwya-gBL^?h1HyXx3(l5';   // DB-Passwort

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
