<?php
//Logout
// Include config file
require_once "config.php";
include 'helper_functions.php';
// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($link, trim($_POST["username"]));
    $query = "UPDATE users SET appLoggedIn=? WHERE username=?";
    $stmt = mysqli_prepare($link, $query);
    $isLoggedIn = 0;
    mysqli_stmt_bind_param($stmt, 'is',  $isLoggedIn, $username);
    mysqli_stmt_execute($stmt);
    echo "logged_out";
}
?>