<?php
/* This script is called periodically from APP every x seconds */
require_once "config.php";
include 'helper_functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

   

 
    // get posted variables from APP
    $UserName = test_input($_POST["UserName"]);
    $ConStatus = test_input($_POST["ConStatus"]);        
    $AppActive = test_input($_POST["AppActive"]);      
     
    // get variables from database
    $query = "SELECT  WishedAction FROM users WHERE username = '$UserName'";
    $stmt = $link->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // update users table with posted variables
    $query = "UPDATE users SET  conStatus = ?,  AppActive = ? WHERE username=?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, 'iis', $ConStatus, $AppActive, $UserName);
    mysqli_stmt_execute($stmt);
    
    // init response variable
    $response = new stdClass();
    if (isset($row["WishedAction"])) {
        $response->WishedAction = $row["WishedAction"];
    } else {
        $response->WishedAction = "";
    }

    // send back response to APP
    echo json_encode($response);
   

    // clear wished action from database after command has been sent to APP
    $query = "UPDATE users SET WishedAction='' WHERE username='$UserName'";
    $stmt = mysqli_prepare($link, $query); 
    mysqli_stmt_execute($stmt);
}
else {
    echo "No data posted with HTTP POST.";
  
}
  
    
?>