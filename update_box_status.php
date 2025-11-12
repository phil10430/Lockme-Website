<?php
/* This script is called when Box sends a response  to APP */
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
    $emergencyDays = test_input($_POST["emergencyDays"]);     
    
    $RtcClcRate = test_input($_POST["RtcClcRate"]);    
    $ccf = test_input($_POST["ccf"]);    
    $TimeDifferenceSec = test_input($_POST["TimeDifferenceSec"]);    
    $SleepTime = test_input($_POST["SleepTime"]);    
    $SoC = test_input($_POST["SoC"]);    
    $firmwareVersion = test_input($_POST["firmwareVersion"]);   

    // get variables from database
    $query = "SELECT LockStatus, OpenTime, WishedAction FROM users WHERE username = '$UserName'";
    $stmt = $link->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    
    // update users table with posted variables
    $query = "UPDATE users SET BoxName=?, ProtectionLevelTimer = ?, ProtectionLevelPassword = ?, LockStatus = ?, emergencyDays = ? , OpenTime = ? , firmwareVersion = ? WHERE username=?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'iiiiisis', 
    $BoxName, 
    $ProtectionLevelTimer, 
    $ProtectionLevelPassword, 
    $LockStatus, 
    $emergencyDays, 
    $OpenTime, 
    $firmwareVersion,
    $UserName 
    );
    mysqli_stmt_execute($stmt);
    

    // if lockstatus has changed or open time was extended update history table
    if(($row["LockStatus"] != $LockStatus) || ($row["OpenTime"] != $OpenTime))
    {
        $query = "INSERT INTO history (
            BoxName, 
            LockStatus, 
            ProtectionLevelTimer,
            ProtectionLevelPassword, 
            OpenTime, 
            username
        ) VALUES (?,?,?,?,?,?)";

        if($stmt = mysqli_prepare($link, $query)){
            mysqli_stmt_bind_param($stmt,"iiiiss", 
            $BoxName, 
            $LockStatus, 
            $ProtectionLevelTimer, 
            $ProtectionLevelPassword, 
            $OpenTime, 
            $UserName
            );      
        }
        mysqli_stmt_execute($stmt); 
    }

    /*-------check when to update box data --------*/
   $query = "INSERT INTO history_boxdata (BoxName, LockStatus, RtcClcRate, ccf, TimeDifferenceSec, SleepTime, SoC, username) VALUES (?,?,?,?,?,?,?,?)";
   if($stmt = mysqli_prepare($link, $query)){
       mysqli_stmt_bind_param($stmt,"iissssss", 
       $BoxName, 
       $LockStatus, 
       $RtcClcRate, 
       $ccf, 
       $TimeDifferenceSec, 
       $SleepTime, 
       $SoC, 
       $UserName);      
   }
   mysqli_stmt_execute($stmt); 
   
}
else {
    echo "No data posted with HTTP POST.";
}
    
?>