<?php
require_once "config.php";
include 'helper_functions.php';

$boxControlError = "";
$message = "";
$username = $_SESSION["username"];

 
if (($conStatus == 1) && ($appLoggedIn == 1)  && ($AppActive == 1)){
    // load box control form
    require "box_control_form.php";
    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $plTimer = "0";
        $plPassword = "0";
        $OpenTimeUnix = PLACEHOLDER; // initialize as placeholder
        

        $OpenTime = test_input($_POST["OpenTime"]);
        $Password = test_input($_POST["Password"]);
        $CloseBoxIntent = isset($_POST['CloseBox']);
        $OpenBoxIntent = isset($_POST['OpenBox']);
        $isTimeProtection = isset($_POST['timeCheckbox']);
        $isPasswordProtection = isset($_POST['passwordCheckbox']);

        if(empty($Password)){
            $Password = PLACEHOLDER;
        }

        if ($CloseBoxIntent) {
        
            if ($isTimeProtection) {
                if (validateDate($OpenTime)) {
                    $dt = DateTime::createFromFormat("d/m/Y H:i", $OpenTime, new DateTimeZone('Europe/Berlin'));
                    $OpenTimeUnix = $dt->getTimestamp();
                    $plTimer = "1";
                } else {
                    $boxControlError = "Invalid date";
                }
            }
            if ($isPasswordProtection) {
                if(($Password != PLACEHOLDER ) && isValidPassword($Password)){
                    $plPassword = "1";
                }else{
                    $boxControlError = "Invalid password! Password must only contain a-z A-Z 0-9 and have 1 to 10 characters.";
                }
            }
            if (empty($boxControlError)){
                $message = MSG_CLOSE . MSG_SEPARATOR .
                    $plTimer . MSG_SEPARATOR . $plPassword . MSG_SEPARATOR .
                    $OpenTimeUnix . MSG_SEPARATOR .
                    $Password;
            }

        } elseif ($OpenBoxIntent) {
            $message = MSG_OPEN . MSG_SEPARATOR .
                $Password;
        }
        
        $query = "UPDATE users SET WishedAction=? WHERE username=?";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $message, $username);
        mysqli_stmt_execute($stmt);
        
    }
}
if (!empty($boxControlError)) {
    echo '<div class="alert alert-danger">' . $boxControlError . '</div>';
}
 
?>