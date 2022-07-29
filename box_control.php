<?php
require_once "config.php";
include 'helper_functions.php';

$boxControlError = "";
$connectionStatus = "";
$username = $_SESSION["username"];



// get variables from Database
$result = mysqli_query($link, "SELECT conStatus, BoxName, appLoggedIn FROM users WHERE username = '$username' ");

while ($row = $result->fetch_assoc()) {
    $conStatus =  $row['conStatus'];
    $appLoggedIn =  $row['appLoggedIn'];
    $BoxName =  $row['BoxName'];
}

if ($appLoggedIn == CON_STATUS_CONNECTED) { 
    if ($conStatus == CON_STATUS_CONNECTED) {
        $connectionStatus =  "Connected to LockMe-Box #" . $BoxName;
    } else {
        $connectionStatus = "App not connected to LockMe-Box. Connect App to LockMe-Box to enable control.";
    }
} else{
    $connectionStatus = "App is not connected to Account. Open your App and login.";
}

echo '<div class="alert alert-info">' . $connectionStatus . '</div>';
 
if (($conStatus == CON_STATUS_CONNECTED) && ($appLoggedIn == CON_STATUS_CONNECTED) ){
    // load box control form
    require "box_control_form.php";
    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $OpenTime = test_input($_POST["OpenTime"]);
        $Password = test_input($_POST["Password"]);
        $CloseBoxIntent = isset($_POST['CloseBox']);
        $OpenBoxIntent = isset($_POST['OpenBox']);
        $isTimeProtection = isset($_POST['timeCheckbox']);
        $isPasswordProtection = isset($_POST['passwordCheckbox']);

        if ($CloseBoxIntent) {
            $plTimer = "0";
            $plPassword = "0";
            $OpenTimeUnix = PLACEHOLDER; // initialize as placeholder
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
            $message = MSG_CLOSE . MSG_SEPARATOR .
                $plTimer . MSG_SEPARATOR . $plPassword . MSG_SEPARATOR .
                $OpenTimeUnix . MSG_SEPARATOR .
                $Password;
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