<?php

// Include config file
require_once "config.php";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
           
        $BoxName = test_input($_POST["BoxName"]);
        $LockStatus = test_input($_POST["LockStatus"]);
        $Protection = test_input($_POST["Protection"]);
        $OpenTime = test_input($_POST["OpenTime"]);    

        $sql = "INSERT INTO history (BoxName, LockStatus, Protection, OpenTime)
        VALUES ('" . $BoxName . "', '" . $LockStatus . "', '" . $Protection . "', '" . $OpenTime . "')";
        
        if ($link->query($sql) === TRUE) {
            echo "New record created successfully";
        } 
        else {
            echo "Error: " . $sql . "<br>" . $link->error;
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
