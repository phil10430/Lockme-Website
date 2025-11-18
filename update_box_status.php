<?php

/* This script is called when Box sends a response  to APP */

require_once "config.php";
include 'helper_functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // get posted variables from APP
    $username = test_input($_POST["username"]); 
    $boxName = test_input($_POST["boxName"]);  
    $protectionLevelTimer = test_input($_POST["protectionLevelTimer"]);
    $protectionLevelPassword = test_input($_POST["protectionLevelPassword"]);
    $lockStatus = test_input($_POST["lockStatus"]);
    $openTime = test_input($_POST["openTime"]);
    $lockedSince = test_input($_POST["lockedSince"]);     
    $firmwareVersion = test_input($_POST["firmwareVersion"]);   

    $sql = "UPDATE users SET 
    box_name = :box_name, 
    protection_level_timer = :protection_level_timer, 
    protection_level_password = :protection_level_password, 
    lock_status = :lock_status, 
    open_time = :open_time, 
    firmware_version = :firmware_version,
    locked_since = :locked_since
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
        ':firmware_version' => $firmwareVersion  
    ]);
   
}
else {
    echo "No data posted with HTTP POST.";
}
    
?>