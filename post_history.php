<?php

// Include config file
require_once "config.php";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
           
        $BoxID = test_input($_POST["BoxID"]);
        $LockStatus = test_input($_POST["LockStatus"]);
        $Protection = test_input($_POST["Protection"]);
        $OpenTime = test_input($_POST["OpenTime"]);
        $SlaveName = test_input($_POST["SlaveName"]);        
        
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 
        
        $sql = "INSERT INTO history (BoxID, LockStatus, Protection, OpenTime, SlaveName)
        VALUES ('" . $BoxID . "', '" . $LockStatus . "', '" . $Protection . "', '" . $OpenTime . "', '" . $SlaveName . "')";
        
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } 
        else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    
        $conn->close();
    

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
