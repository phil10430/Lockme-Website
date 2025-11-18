<?php
session_start();

require_once "config.php";
include 'helper_functions.php';

$boxControlError = "";
$wishedAction = "";
$username = $_SESSION["username"];

 
if (($conStatus == 1) && ($appLoggedIn == 1)  && ($appActive == 1)){
   
    require "box_control_form.php";
   
    if ($_SERVER["REQUEST_METHOD"] == "POST") {  // Processing form data when form is submitted

        $protectionLevelTimer = "0";
        $protectionLevelPassword = "0";
        $openTimeUnix = PLACEHOLDER; // initialize as placeholder
        

        $openTime = test_input($_POST["openTime"]);
        $password = test_input($_POST["password"]);
        $closeBoxIntent = isset($_POST['closeBox']);
        $setTimerIntent = isset($_POST['setTimer']);
        $openBoxIntent = isset($_POST['openBox']);
        $isTimeProtection = isset($_POST['timeCheckbox']);
        $isPasswordProtection = isset($_POST['passwordCheckbox']);

        if(empty($password )){
            $password  = PLACEHOLDER;
        }

        if ($closeBoxIntent) {
        
            if ($isTimeProtection) {
                if (validateDate($openTime)) {
                    $dt = DateTime::createFromFormat("d/m/Y H:i", $openTime, new DateTimeZone('Europe/Berlin'));
                    $openTimeUnix = $dt->getTimestamp();
                    $protectionLevelTimer = "1";
                } else {
                    $boxControlError = "Invalid date";
                }
            }
            if ($isPasswordProtection) {
                if(($password  != PLACEHOLDER ) && isValidPassword($password )){
                    $protectionLevelPassword = "1";
                }else{
                    $boxControlError = "Invalid password! Password must only contain a-z A-Z 0-9 and have 1 to 10 characters.";
                }
            }
            if (empty($boxControlError)){
                $wishedAction = MSG_CLOSE . MSG_SEPARATOR .
                    $protectionLevelTimer . MSG_SEPARATOR . $protectionLevelPassword . MSG_SEPARATOR .
                    $openTimeUnix . MSG_SEPARATOR .
                    $password ;
            }
        } elseif ($openBoxIntent) 
        {
            $wishedAction = MSG_OPEN . MSG_SEPARATOR .
                $password ;
        } elseif ($setTimerIntent)
        {
            if (validateDate($openTime)) {
                $dt = DateTime::createFromFormat("d/m/Y H:i", $openTime, new DateTimeZone('Europe/Berlin'));
                $openTimeUnix = $dt->getTimestamp();
                $protectionLevelTimer = "1";
            } else {
                $boxControlError = "Invalid date";
            }
            if (empty($boxControlError)){
                $wishedAction = EXTEND_OPEN_TIME . MSG_SEPARATOR .
                    $protectionLevelTimer . MSG_SEPARATOR .
                    $openTimeUnix . MSG_SEPARATOR .
                    $password ;
            }
        }
        
     
        $sql = "UPDATE users SET wished_action = :wished_action WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':wished_action' => $wishedAction,
            ':username' => $username
        ]);

    }
}
if (!empty($boxControlError)) {
    echo '<div class="alert alert-danger">' . $boxControlError . '</div>';
}
 
?>