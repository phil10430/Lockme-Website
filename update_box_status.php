<?php

/* This script is called when Box sends a response  to APP */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helper_functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // get posted variables from APP
//    $username = test_input($_POST["username"]); 
    $boxName = test_input($_POST["boxName"]);  
    $protectionLevelTimer = test_input($_POST["protectionLevelTimer"]);
    $protectionLevelPassword = test_input($_POST["protectionLevelPassword"]);
    $lockStatus = test_input($_POST["lockStatus"]);
    $openTime = test_input($_POST["openTime"]);
    $lockedSince = test_input($_POST["lockedSince"]); 
    $timeLeft = test_input($_POST["timeLeft"]);       
    $firmwareVersion = test_input($_POST["firmwareVersion"]);   
    $rtc_logCountOpenCloseCycles_String = test_input($_POST["rtc_logCountOpenCloseCycles_String"]);   
    $rtc_logCountSwitchCycles_String = test_input($_POST["rtc_logCountSwitchCycles_String"]);   
    $rtc_logOnTimeSec_String = test_input($_POST["rtc_logOnTimeSec_String"]);   
    $hardwareVersion = test_input($_POST["hardwareVersion"]);
    

   /*
    if($proVersion=="1") {
        $sql = "UPDATE users SET 
        box_name_con = :box_name, 
        pro_version = :pro_version
        WHERE username = :username";
        
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':username' => $username,
            ':box_name_con' => $boxName,       
            ':pro_version' => $proVersion      
        ]);
    }
*/

    $sql = "INSERT INTO box_data_actual
            (
                box_name, 
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
                hardware_version
            ) 
    VALUES 
            (
                :box_name, 
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
                :hardware_version
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
                hardware_version          = VALUES(hardware_version)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':box_name'                  => $boxName,       
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
        ':hardware_version'          => $hardwareVersion  
    ]);





    // Letzte History-Zeile für diese Box holen
    $sql_check = "SELECT firmware_version, log_openclosecycles, log_switchcycles, log_ontimesec,
                        lock_status, open_time, locked_since, time_left,
                        protection_level_timer, protection_level_password, hardware_version
                FROM box_data_history
                WHERE box_name = :box_name
                ORDER BY id DESC
                LIMIT 1";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([':box_name' => $boxName]);
    $last = $stmt_check->fetch(PDO::FETCH_ASSOC);

    // Vergleichen ob sich etwas geändert hat
    $hasChanged = !$last || 
        $last['firmware_version']          !== $firmwareVersion ||
        $last['log_openclosecycles']       !== $rtc_logCountOpenCloseCycles_String ||
        $last['log_switchcycles']          !== $rtc_logCountSwitchCycles_String ||
        $last['log_ontimesec']             !== $rtc_logOnTimeSec_String ||
        $last['lock_status']               !== $lockStatus ||
        $last['open_time']                 !== $openTime ||
        $last['locked_since']              !== $lockedSince ||
        $last['time_left']                 !== $timeLeft ||
        $last['protection_level_timer']    !== $protectionLevelTimer ||
        $last['protection_level_password'] !== $protectionLevelPassword ||
        $last['hardware_version']          !== $hardwareVersion;

    // Nur inserten wenn sich etwas geändert hat
    if ($hasChanged) {
        $sql_history = "INSERT INTO box_data_history
                        (
                            box_name, 
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
                            hardware_version
                        ) 
        VALUES 
                        (
                            :box_name, 
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
                            :hardware_version
                        )";
        $stmt_history = $pdo->prepare($sql_history);
        $stmt_history->execute([
            ':box_name'                  => $boxName,
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
            ':hardware_version'          => $hardwareVersion
        ]);
    }
    
}
else {
    echo "No data posted with HTTP POST.";
}
  
?>