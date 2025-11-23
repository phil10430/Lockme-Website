<?php

/* This script is called when Box sends a response  to APP */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helper_functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // get posted variables from APP
    $username = test_input($_POST["username"]); 
    $boxName = test_input($_POST["boxName"]);  
    $protectionLevelTimer = test_input($_POST["protectionLevelTimer"]);
    $protectionLevelPassword = test_input($_POST["protectionLevelPassword"]);
    $lockStatus = test_input($_POST["lockStatus"]);
    $openTime = test_input($_POST["openTime"]);
    $lockedSince = test_input($_POST["lockedSince"]); 
    $timeLeft = test_input($_POST["timeLeft"]);       
    $firmwareVersion = test_input($_POST["firmwareVersion"]);   
    $rtc_logCountSwitchCycles_String = test_input($_POST["rtc_logCountSwitchCycles_String"]);   
    $rtc_logCountSwitchCycles_String = test_input($_POST["rtc_logCountSwitchCycles_String"]);   
    $rtc_logOnTimeSec_String = test_input($_POST["rtc_logOnTimeSec_String"]);   
    $hardwareVersion = test_input($_POST["hardwareVersion"]);

    $sql = "UPDATE users SET 
    box_name = :box_name, 
    protection_level_timer = :protection_level_timer, 
    protection_level_password = :protection_level_password, 
    lock_status = :lock_status, 
    open_time = :open_time, 
    firmware_version = :firmware_version,
    locked_since = :locked_since,
    time_left = :time_left,
    log_openclosecycles = :log_openclosecycles, 
    log_switchcycles = :log_switchcycles,
    log_ontimesec = :log_ontimesec,
    hardware_version = :hardware_version
    WHERE username = :username";
    
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':username' => $username,
        ':box_name' => $boxName,       
        ':lock_status' => $lockStatus,  
        ':protection_level_timer' => $protectionLevelTimer,     
        ':protection_level_password' => $protectionLevelPassword,    
        ':open_time' => $openTime,
        ':locked_since' => $lockedSince,    
        ':time_left' => $timeLeft,    
        ':firmware_version' => $firmwareVersion,  
        ':log_openclosecycles' => $rtc_logCountSwitchCycles_String,
        ':log_switchcycles' => $rtc_logCountSwitchCycles_String,    
        ':log_ontimesec' => $rtc_logOnTimeSec_String,    
        ':hardware_version' => $hardwareVersion  
    ]);
   
}
else {
    echo "No data posted with HTTP POST.";
}
    
?>