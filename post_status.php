<?php

/* This script is called periodically from Android APP every second  */


require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helper_functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // get posted variables from APP
    $username = test_input($_POST["username"]);
    $boxName = test_input($_POST["boxName"]);     
    $appActive = test_input($_POST["appActive"]);      
    $proVersion = test_input($_POST["proVersion"]);

    
    $query = "SELECT wished_action FROM users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':username' => $username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
   

    $sql = "UPDATE users SET 
    app_active = :app_active,
    box_name_con = :box_name_con
    WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':app_active' => $appActive,
        ':box_name_con' => $boxName,
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