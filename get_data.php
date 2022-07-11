<?php
require_once "config.php";
$UserName = $_POST["UserName"];
$query = "SELECT WishedAction FROM users WHERE username = '$UserName'";

    $result = mysqli_query($link, $query);
    $number_of_rows = mysqli_num_rows($result);

    $response = array();

    if($number_of_rows > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $response[] = $row;
        }
    }
    header('Content-Type: application/json');
    echo json_encode(array("users"=>$response));
?>