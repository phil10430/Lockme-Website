<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {    
        $ConStatus = test_input($_POST["ConStatus"]);        
        $UserName = test_input($_POST["UserName"]);   
        $WishedAction = test_input($_POST["WishedAction"]);  
        $BoxName = test_input($_POST["BoxName"]);  

        $sql = "UPDATE users SET conStatus=$ConStatus, WishedAction='$WishedAction' WHERE username='$UserName'";
        
        if ($link->query($sql) === TRUE) {
            echo "New record created successfully";
        } 
        else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
}
else {
    echo "No data posted with HTTP POST.";
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>