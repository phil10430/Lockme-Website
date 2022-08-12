<?php
/* This script is called periodically from APP every x seconds */
require_once "config.php";
include 'helper_functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // get posted variables from APP
    $UserName = test_input($_POST["UserName"]); 
    $BoxName = test_input($_POST["BoxName"]);  
    $LockStatus = test_input($_POST["LockStatus"]);
    $ProtectionLevelTimer = test_input($_POST["ProtectionLevelTimer"]);
    $ProtectionLevelPassword = test_input($_POST["ProtectionLevelPassword"]);
    $OpenTime = test_input($_POST["OpenTime"]);    
    
    // get variables from database
    $query = "SELECT LockStatus, OpenTime, WishedAction FROM users WHERE username = '$UserName'";
    $stmt = $link->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
 
    // update users table with posted variables
    $query = "UPDATE users SET BoxName=?, ProtectionLevelTimer = ?, 
    ProtectionLevelPassword = ?, LockStatus = ?, OpenTime = ? WHERE username=?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'iiiiss', $BoxName, $ProtectionLevelTimer, 
    $ProtectionLevelPassword, $LockStatus,  $OpenTime, $UserName);
    mysqli_stmt_execute($stmt);

    // if lockstatus has changed or open time was extended update history table
    if(($row["LockStatus"] != $LockStatus) || ($row["OpenTime"] != $OpenTime))
    {
        $query = "INSERT INTO history (BoxName, LockStatus, ProtectionLevelTimer,
         ProtectionLevelPassword, OpenTime, username) VALUES (?,?,?,?,?,?)";
        if($stmt = mysqli_prepare($link, $query)){
            mysqli_stmt_bind_param($stmt,"iiiiss", $BoxName, $LockStatus, $ProtectionLevelTimer, 
            $ProtectionLevelPassword, $OpenTime, $UserName);      
        }
        mysqli_stmt_execute($stmt); 
    }
}
else {
    echo "No data posted with HTTP POST.";
}
    
?>