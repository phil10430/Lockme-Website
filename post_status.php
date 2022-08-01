<?php
require_once "config.php";
include 'helper_functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // get posted variables from APP
    $RequestType = test_input($_POST["RequestType"]); 
    $ConStatus = test_input($_POST["ConStatus"]);        
    $UserName = test_input($_POST["UserName"]);   
    $Password = test_input($_POST["Password"]);
    $BoxName = test_input($_POST["BoxName"]);  
    $LockStatus = test_input($_POST["LockStatus"]);
    $ProtectionLevelTimer = test_input($_POST["ProtectionLevelTimer"]);
    $ProtectionLevelPassword = test_input($_POST["ProtectionLevelPassword"]);
    $OpenTime = test_input($_POST["OpenTime"]);    
    
    // get old variabes from database
    $query = "SELECT LockStatus, OpenTime FROM users WHERE username = '$UserName'";
    $stmt = $link->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
 
    // if lockstatus has changed or open time was extended update history table
    if($row["LockStatus"] != $LockStatus){
        $query = "INSERT INTO history (BoxName, LockStatus, ProtectionLevelTimer,
         ProtectionLevelPassword, OpenTime, username) VALUES (?,?,?,?,?,?)";
        if($stmt = mysqli_prepare($link, $query)){
            mysqli_stmt_bind_param($stmt,"iiiiss", $BoxName, $LockStatus, $ProtectionLevelTimer, $ProtectionLevelPassword, $OpenTime, $UserName);      
        }
        mysqli_stmt_execute($stmt); 
        
    }

    // general status update
    $query = "UPDATE users SET BoxName=?, conStatus = ?, ProtectionLevelTimer = ?, 
    ProtectionLevelPassword = ?, LockStatus = ?, OpenTime = ? WHERE username=?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'iiiiiss', $BoxName, $ConStatus, $ProtectionLevelTimer, 
    $ProtectionLevelPassword, $LockStatus,  $OpenTime, $UserName);
    mysqli_stmt_execute($stmt);

    $response->WishedAction = "";
    
    if ($RequestType == REQUEST_CLEAR_WISHED_ACTION){
         // clear wished action from database when status is updated
         $query = "UPDATE users SET WishedAction='' WHERE username='$UserName'";
         $stmt = mysqli_prepare($link, $query); 
         mysqli_stmt_execute($stmt);
    }
    else {
        // send back WishedAction to APP
        $query = "SELECT WishedAction FROM users WHERE username = '$UserName'";
        $result = mysqli_query($link, $query);
        $number_of_rows = mysqli_num_rows($result);
        $response = mysqli_fetch_assoc($result);
       
    }
    echo json_encode($response);
    
}
else {
    echo "No data posted with HTTP POST.";
}

    
?>