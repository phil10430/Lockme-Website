<?php
require_once "config.php";

const MSG_OPEN = "O";
const MSG_CLOSE = "C";
const MSG_SEPARATOR = "/";
const CON_STATUS_CONNECTED = 1;
const PLACEHOLDER = "*";


$boxControlError = "";
$connectionStatus = "";
$name = $_SESSION["username"];

// get variables from Database
$result = mysqli_query($link, "SELECT conStatus, BoxName, appLoggedIn FROM users WHERE username = '$name'");

while ($row = $result->fetch_assoc()) {
    $conStatus =  $row['conStatus'];
    $appLoggedIn =  $row['appLoggedIn'];
    $BoxName =  $row['BoxName'];
}

if ($appLoggedIn == CON_STATUS_CONNECTED) { 
    if ($conStatus == CON_STATUS_CONNECTED) {
        $connectionStatus =  "Connected to " . $BoxName;
    } else {
        $connectionStatus = "Not connected to LockMe-Box. Connect App to LockMe-Box to enable control.";
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
                if($Password != PLACEHOLDER){
                    $plPassword = "1";
                }else{
                    $boxControlError = "Invalid password";
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

        $sql = "UPDATE users SET WishedAction='$message' WHERE username='$name'";
        $link->query($sql);
    }
}

 
        if (!empty($boxControlError)) {
            echo '<div class="alert alert-danger">' . $boxControlError . '</div>';
        }
 

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateDate($date)
{
    $format = 'd/m/Y H:i'; // Eg : 21/07/2022 14:40
    $dateTime = DateTime::createFromFormat($format, $date);

    if ($dateTime instanceof DateTime && $dateTime->format('d/m/Y H:i') == $date) {
        return true;
    }

    return false;
}
