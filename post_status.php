<?php

/* This script is called periodically from Android APP every second  */


require_once "config.php";
include 'helper_functions.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // get posted variables from APP
    $username = test_input($_POST["username"]);
    $conStatus = test_input($_POST["conStatus"]);        
    $appActive = test_input($_POST["appActive"]);      
    
    $query = "SELECT wished_action FROM users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':username' => $username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
   

    $sql = "UPDATE users SET con_status = :con_status, app_active = :app_active WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':app_active' => $appActive,
        ':con_status' => $conStatus,
        ':username' => $username
    ]);

    
    // init response variable
    $response = new stdClass();
    if (isset($row["wished_action"])) {
        $response->wishedAction = $row["wished_action"];
    } else {
        $response->wishedAction = "";
    }

    // send back response to APP
    echo json_encode($response);
   
    
    // clear wished action from database after command has been sent to APP
    $sql = "UPDATE users SET wished_action = :wished_action WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':wished_action'        => '',
        ':username' => $username
    ]);
    
}
else {
    echo "No data posted with HTTP POST.";

  
}
 
  
?>