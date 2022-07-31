<?php
// get lock/con-status from database and send it back to ajax script
require "config.php"; 
if($_SERVER["REQUEST_METHOD"] == "POST"){
   
    $username = mysqli_real_escape_string($link,trim($_POST["username"]));

    $query = "SELECT LockStatus, conStatus, appLoggedIn, OpenTime
    FROM users WHERE username = '$username'";

    $stmt = $link->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
  
    echo json_encode($row);
}
?>
