<?php
// get lock-status from database and send it back to ajax script
require "config.php"; 
$username = $_POST["username"];
$query = "SELECT LockStatus FROM users WHERE username = '$username'";

$stmt = $link->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    //$response[] = $row;
    $LockStatus = $row["LockStatus"];
}

 //echo json_encode(array("users"=>$response));
 echo  $LockStatus;
?>
