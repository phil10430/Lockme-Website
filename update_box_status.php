<?php

/* This script is called when Box sends a response to APP */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helper_functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo "No data posted with HTTP POST.";
    exit;
}

$boxName                         = test_input($_POST["boxName"] ?? '');
$protectionLevelTimer            = test_input($_POST["protectionLevelTimer"] ?? '');
$protectionLevelPassword         = test_input($_POST["protectionLevelPassword"] ?? '');
$lockStatus                      = test_input($_POST["lockStatus"] ?? '');
$openTime                        = test_input($_POST["openTime"] ?? '');
$lockedSince                     = test_input($_POST["lockedSince"] ?? '');
$timeLeft                        = test_input($_POST["timeLeft"] ?? '');
$firmwareVersion                 = test_input($_POST["firmwareVersion"] ?? '');
$rtc_logCountOpenCloseCycles_String = test_input($_POST["rtc_logCountOpenCloseCycles_String"] ?? '');
$rtc_logCountSwitchCycles_String = test_input($_POST["rtc_logCountSwitchCycles_String"] ?? '');
$rtc_logOnTimeSec_String         = test_input($_POST["rtc_logOnTimeSec_String"] ?? '');
$hardwareVersion                 = test_input($_POST["hardwareVersion"] ?? '');
$emergencyPasswordUsedCounter    = test_input($_POST["emergencyPasswordUsedCounter"] ?? '');

// ============================================
// Validierung ZUERST, vor jeglicher DB-Interaktion
// Nur 6-stellige numerische Box-IDs sind gültig
// ============================================
if (!preg_match('/^\d{6}$/', $boxName)) {
    http_response_code(400);
    exit('Invalid box ID: exactly 6 digits are required.');
}

// ============================================
// App-User (Besitzer, der in der App eingeloggt ist)
// ============================================
$userId = $_SESSION["id"] ?? null;
if (!$userId) {
    http_response_code(401);
    exit('Not logged in.');
}

$appUsername = $_SESSION['username'] ?? '';
if ($appUsername === '') {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
    $stmt->execute([':id' => $userId]);
    $appUsername = (string)$stmt->fetchColumn();
}

// ============================================
// Alten Lock-Zustand lesen, BEVOR box_data_actual überschrieben wird
// ============================================
$stmt = $pdo->prepare("SELECT lock_status FROM box_data_actual WHERE box_id = :box_id");
$stmt->execute([':box_id' => $boxName]);
$prev = $stmt->fetch(PDO::FETCH_ASSOC);
$wasLocked = $prev && (int)$prev['lock_status'] === 1;

// ============================================
// Aktuellen Status schreiben
// ============================================
$sql = "INSERT INTO box_data_actual
        (
            box_id,
            firmware_version,
            log_openclosecycles,
            log_switchcycles,
            log_ontimesec,
            lock_status,
            open_time,
            locked_since,
            time_left,
            protection_level_timer,
            protection_level_password,
            hardware_version,
            emergency_password_used
        )
VALUES
        (
            :box_id,
            :firmware_version,
            :log_openclosecycles,
            :log_switchcycles,
            :log_ontimesec,
            :lock_status,
            :open_time,
            :locked_since,
            :time_left,
            :protection_level_timer,
            :protection_level_password,
            :hardware_version,
            :emergency_password_used
        )
ON DUPLICATE KEY UPDATE
            firmware_version          = VALUES(firmware_version),
            log_openclosecycles       = VALUES(log_openclosecycles),
            log_switchcycles          = VALUES(log_switchcycles),
            log_ontimesec             = VALUES(log_ontimesec),
            lock_status               = VALUES(lock_status),
            open_time                 = VALUES(open_time),
            locked_since              = VALUES(locked_since),
            time_left                 = VALUES(time_left),
            protection_level_timer    = VALUES(protection_level_timer),
            protection_level_password = VALUES(protection_level_password),
            hardware_version          = VALUES(hardware_version),
            emergency_password_used   = VALUES(emergency_password_used)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':box_id'                    => $boxName,
    ':firmware_version'          => $firmwareVersion,
    ':log_openclosecycles'       => $rtc_logCountOpenCloseCycles_String,
    ':log_switchcycles'          => $rtc_logCountSwitchCycles_String,
    ':log_ontimesec'             => $rtc_logOnTimeSec_String,
    ':lock_status'               => $lockStatus,
    ':open_time'                 => $openTime,
    ':locked_since'              => $lockedSince,
    ':time_left'                 => $timeLeft,
    ':protection_level_timer'    => $protectionLevelTimer,
    ':protection_level_password' => $protectionLevelPassword,
    ':hardware_version'          => $hardwareVersion,
    ':emergency_password_used'   => $emergencyPasswordUsedCounter
]);

// ============================================
// History-Insert mit Race-Condition-Schutz
// (Transaction + FOR UPDATE Lock)
// ============================================
$pdo->beginTransaction();

try {
    $sql_check = "SELECT firmware_version, log_openclosecycles, log_switchcycles, log_ontimesec,
                         lock_status, open_time,
                         protection_level_timer, protection_level_password, hardware_version, emergency_password_used
                  FROM box_data_history
                  WHERE box_id = :box_id
                  ORDER BY id DESC
                  LIMIT 1
                  FOR UPDATE";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':box_id' => $boxName]);
    $last = $stmt_check->fetch(PDO::FETCH_ASSOC);

    // Normalisierte Vergleichsfunktion (NULL und '' gelten als gleich)
    $normalize = fn($v) => $v === null ? '' : strval($v);

    $hasChanged = !$last ||
        $normalize($last['firmware_version'])         !== $normalize($firmwareVersion) ||
        $normalize($last['lock_status'])              !== $normalize($lockStatus) ||
        $normalize($last['open_time'])                !== $normalize($openTime) ||
        $normalize($last['protection_level_timer'])   !== $normalize($protectionLevelTimer) ||
        $normalize($last['protection_level_password']) !== $normalize($protectionLevelPassword) ||
        $normalize($last['hardware_version'])         !== $normalize($hardwareVersion);

    if ($hasChanged) {
        $sql_history = "INSERT INTO box_data_history
                        (
                            box_id,
                            firmware_version,
                            log_openclosecycles,
                            log_switchcycles,
                            log_ontimesec,
                            lock_status,
                            open_time,
                            protection_level_timer,
                            protection_level_password,
                            hardware_version,
                            emergency_password_used
                        )
        VALUES
                        (
                            :box_id,
                            :firmware_version,
                            :log_openclosecycles,
                            :log_switchcycles,
                            :log_ontimesec,
                            :lock_status,
                            :open_time,
                            :protection_level_timer,
                            :protection_level_password,
                            :hardware_version,
                            :emergency_password_used
                        )";
        $stmt_history = $pdo->prepare($sql_history);
        $stmt_history->execute([
            ':box_id'                    => $boxName,
            ':firmware_version'          => $firmwareVersion,
            ':log_openclosecycles'       => $rtc_logCountOpenCloseCycles_String,
            ':log_switchcycles'          => $rtc_logCountSwitchCycles_String,
            ':log_ontimesec'             => $rtc_logOnTimeSec_String,
            ':lock_status'               => $lockStatus,
            ':open_time'                 => $openTime,
            ':protection_level_timer'    => $protectionLevelTimer,
            ':protection_level_password' => $protectionLevelPassword,
            ':hardware_version'          => $hardwareVersion,
            ':emergency_password_used'   => $emergencyPasswordUsedCounter
        ]);
    }

    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log($e->getMessage());
    http_response_code(500);
    exit('Error writing history.');
}

// ============================================
// Wechsel offen -> verschlossen: Keyholder festlegen
// Vorgemerkter Keyholder (Website, max. 5 Min. alt) hat Vorrang,
// sonst der App-User (Verschließen über App oder Gerät).
// ============================================
if ((int)$lockStatus === 1 && !$wasLocked) {
    try {
        $stmt = $pdo->prepare("INSERT INTO box_details (box_id, keyholder, keyholder_user_id)
                               VALUES (:box_id, :app_user, :app_uid)
                               ON DUPLICATE KEY UPDATE
                                 keyholder_user_id = IF(pending_keyholder_user_id IS NOT NULL
                                                        AND pending_keyholder_at > NOW() - INTERVAL 5 MINUTE,
                                                        pending_keyholder_user_id, VALUES(keyholder_user_id)),
                                 keyholder         = IF(pending_keyholder IS NOT NULL
                                                        AND pending_keyholder_at > NOW() - INTERVAL 5 MINUTE,
                                                        pending_keyholder, VALUES(keyholder)),
                                 pending_keyholder = NULL,
                                 pending_keyholder_user_id = NULL,
                                 pending_keyholder_at = NULL");
        $stmt->execute([':box_id' => $boxName, ':app_user' => $appUsername, ':app_uid' => (int)$userId]);
    } catch (Throwable $e) {
        error_log('Keyholder update failed: ' . $e->getMessage());
    }
}

// ============================================
// Box-Registrierung (user_boxes)
// ============================================
$stmt = $pdo->prepare("INSERT IGNORE INTO user_boxes (user_id, box_id, registered_at)
                       VALUES (:user_id, :box_id, NOW())");
$stmt->execute([
    ':user_id' => $userId,
    ':box_id'  => $boxName,
]);

if ($stmt->rowCount() === 0) {
    echo "This box is already registered.";
} else {
    echo "Box successfully registered.";
}
?>