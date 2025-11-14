<?php

/* This script is called when Box sends a response  to APP */

require_once "config.php";
include 'helper_functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // get posted variables from APP
    $username = test_input($_POST["username"]); 
    $boxName = test_input($_POST["boxName"]);  
    $lockStatus = test_input($_POST["lockStatus"]);
    $protectionLevelTimer = test_input($_POST["protectionLevelTimer"]);
    $protectionLevelPassword = test_input($_POST["protectionLevelPassword"]);
    $openTime = test_input($_POST["openTime"]); 
    $emergencyDays = test_input($_POST["emergencyDays"]);  
    $firmwareVersion = test_input($_POST["firmwareVersion"]);   

    $sql = "UPDATE users SET 
    box_name = :box_name, 
    protection_level_timer = :protection_level_timer, 
    protection_level_password = :protection_level_password, 
    lock_status = :lock_status, 
    emergency_days = :emergency_days, 
    open_time = :open_time, 
    firmware_version = :firmware_version
    WHERE username = :username";
    
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':username' => $username,
        ':box_name' => $boxName,       
        ':lock_status' => $lockStatus,  
        ':protection_level_timer' => $protectionLevelTimer,     
        ':protection_level_password' => $protectionLevelPassword,    
        ':open_time' => $openTime,
        ':emergency_days' => $emergencyDays,    
        ':firmware_version' => $firmwareVersion  
    ]);
   
}
else {
    echo "No data posted with HTTP POST.";
}
    
?>