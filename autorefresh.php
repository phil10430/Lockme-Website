<?php
// get lock-status from database and send it back to ajax script
require "config.php"; 
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // $username = $_POST["username"];
    $username = mysqli_real_escape_string($link,trim($_POST["username"]));
    $query = "SELECT LockStatus, conStatus, appLoggedIn, OpenTime
    FROM users WHERE username = '$username'";

    $stmt = $link->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        echo json_encode($row);
       // $LockStatus = $row["LockStatus"];
}

// only export first line

 //echo  $LockStatus;
}
?>
