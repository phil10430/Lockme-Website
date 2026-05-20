<?php
session_start();

include __DIR__ . '/templates/open_dialog.php'; 
include __DIR__ . '/templates/lock_dialog.php'; 
include __DIR__ . '/templates/lock_dialog_random_time.php'; 

// DEBUG -> coomment out autorefresh in header.php
define('DEBUG_WEBSITE', false);

$username = $_SESSION["username"];
$sql = "SELECT * FROM users WHERE username = :username";
$stmt = $pdo->prepare($sql);
$stmt->execute([':username' => $username]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$appLoggedIn = $row['app_logged_in'];
$appActive = $row['app_active'];
$proVersion = $row['pro_version'];
$boxName = $row['box_name_con'];

$sql = "SELECT * FROM box_data_actual WHERE box_name = :box_name";
$stmt = $pdo->prepare($sql);
$stmt->execute([':box_name' => $boxName]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$lockStatus = $row['lock_status'];
$protectionLevelTimer    = $row['protection_level_timer'];
$protectionLevelPassword = $row['protection_level_password'];
$openTime    = $row['open_time'];
$lockedSince = $row['locked_since'];
$timeLeft    = $row['time_left'];
 


if (DEBUG_WEBSITE) {
    $lockStatus = 0;
    $appLoggedIn = 1;
    $appActive = 1;
    $protectionLevelPassword = 0;
    $protectionLevelTimer = 0;
    $boxName = 191919;  
    $timeLeft = '5h left';
    $lockedSince ='since 3 day';
    $openTime  ='2026/05/03 18:10';
}

if (!empty($openTime)) {
    $openTime = date("Y-m-d H:i", strtotime($openTime));
}

if ($proVersion == "1") {


echo '<div class="overlay-card">';

// Hintergrundbild
echo '<img id="bg-image" class="bg-image" alt="Background" ';
if (($appLoggedIn==1) && ($boxName!=0) && ($appActive==1)) {
    if ($lockStatus == 0) {
        echo 'src="/assets/images/icon_box_open.png">';
    } else {
        echo 'src="/assets/images/icon_box_closed.png">';
    }
} else {
    echo 'src="/assets/images/lmb_start.png">';
}

echo '<div class="card-content">';

// Status-Nachricht
$connectionStatusMessage = "";
if ($appActive == 1) {
    if ($appLoggedIn == 1) {
        if ($boxName != 0) {
            $connectionStatusMessage = "LMB " . $boxName;
        } else {
            $connectionStatusMessage = "Connect app to your LockMeBox.";
        }
    } else {
        $connectionStatusMessage = "Open app and login.";
    }
} else {
    $connectionStatusMessage = "Open app for remote control.";
}
echo '<div id="status-message" class="status-message">' . $connectionStatusMessage . '</div>';

echo '<div id="box-control-area">';

// box-control-form
$displayForm =   (($appLoggedIn==1) && ($boxName!=0) && ($appActive==1)) ? '' : 'display:none;';
$displayLocked = (($appLoggedIn==1) && ($boxName!=0) && ($appActive==1) && ($lockStatus==1)) ? '' : 'display:none;';

$timerActive    = ($appLoggedIn==1 && $boxName!=0 && $appActive==1 && $lockStatus==1 && $protectionLevelTimer==1);
$passwordActive = ($appLoggedIn==1 && $boxName!=0 && $appActive==1 && $lockStatus==1 && $protectionLevelPassword==1);

$displayTimer   = $timerActive    ? '' : 'display:none;';
$displayPw      = $passwordActive ? '' : 'display:none;';
$displayWrapper = ($timerActive || $passwordActive) ? '' : 'display:none;';


echo '<div id="box-control-form" style="' . $displayForm . '">';
include __DIR__ . '/templates/button_states.php';
include __DIR__ . '/templates/box_control_form.php';
echo '</div>';

// locked-text & locked-since
echo '<div id="locked-text" class="locked-text" style="' . $displayLocked . '">LOCKED<br></div>';
echo '<div id="locked-since" class="locked-since" style="' . $displayLocked . '">' . $lockedSince . '<br></div>';

// time-left & open-time
echo '<div id="time-left" class="time-left" style="' . $displayTimer . '">' . $timeLeft . '</div>';
echo '<div id="open-time" class="end-time" style="' . $displayTimer . '">' . $openTime . '</div>';

// Wrapper
echo '<div id="protection-level-wrapper" class="protection-level-wrapper" style="' . $displayWrapper . '">';
echo '<div id="password-symbol" style="' . $displayPw . '"><img class="password-symbol" src="/assets/images/lockme_symbol_password.png"></div>';
echo '<div id="timer-symbol" style="' . $displayTimer . '"><img class="timer-symbol" src="/assets/images/lockme_symbol_timer.png"></div>';
echo '</div>';

echo '</div>';


echo '</div>'; // box-control-area
echo '</div>'; // card-content


echo '</div>'; // overlay-card

  
} else {

echo '<div class="overlay-card">'; // Hintergrundbild 
echo '<img id="bg-image" class="bg-image" alt="Background" src="/assets/images/lmb_start.png">';
include __DIR__ . '/templates/get_pro_form.php'; 
echo '</div>'; // overlay-card

}

echo '<div class="card-footer">';

echo '</div>';
?>