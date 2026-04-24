<?php
session_start();
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
$closeButtonText = "CLOSE";

if (!empty($openTime)) {
    $openTime = date("y-m-d H:i", strtotime($openTime));
}

echo '<div class="overlay-card">';

// Hintergrundbild mit ID
echo '<img id="bg-image" class="bg-image" alt="Background" ';
if (($appLoggedIn==1) && ($boxName!=0) && ($appActive==1)) {
    if ($lockStatus == 0) {
        $closeButtonText = "CLOSE";
        echo 'src="/assets/images/icon_box_open.png">';
    } elseif ($lockStatus == 1) {
        $closeButtonText = "OPEN";
        echo 'src="/assets/images/icon_box_closed.png">';
    }
} else {
    echo 'src="/assets/images/icon_box_unclear.png">';
}

echo '<div class="card-content">';

// Status-Nachricht mit ID
$connectionStatusMessage = "";
if ($appActive == 1) {
    if ($appLoggedIn == 1) {
        if ($boxName != 0) {
            $connectionStatusMessage = "Box #" . $boxName;
        } else {
            $connectionStatusMessage = "Connect app to your LOCKMEBOX.";
        }
    } else {
        $connectionStatusMessage = "Open app and login.";
    }
} else {
    $connectionStatusMessage = "Open app to enable control.";
}
echo '<div id="status-message" class="status-message">' . $connectionStatusMessage . '</div>';

// Box-Control-Bereich mit ID
echo '<div id="box-control-area">';
if (($appLoggedIn==1) && ($boxName!=0) && ($appActive==1)) {
    if ($lockStatus == 1) {
        echo '<div id="locked-since" class="locked-since">' . $lockedSince . '<br></div>';
    } else {
        echo '<div id="locked-since" class="locked-since" style="display:none"></div>';
    }

    if (($protectionLevelTimer==1) && ($protectionLevelPassword==1)) {
        echo '<div id="time-left" class="time-left">' . $timeLeft . '</div>';
        echo '<div id="open-time" class="protection-level-timer">' . $openTime . '</div>';
        echo '<div id="password-symbol" class="protection-level-password"><img class="password-symbol" src="/assets/images/lockme_symbol_password.png"></div>';
    } elseif ($protectionLevelTimer==1) {
        echo '<div id="time-left" class="time-left">' . $timeLeft . '</div>';
        echo '<div id="open-time" class="protection-level-timer">' . $openTime . '</div>';
        echo '<div id="password-symbol" class="protection-level-password" style="display:none"></div>';
    } elseif ($protectionLevelPassword==1) {
        echo '<div id="time-left" class="time-left" style="display:none"></div>';
        echo '<div id="open-time" class="protection-level-timer" style="display:none"></div>';
        echo '<div id="password-symbol" class="protection-level-password"><img class="password-symbol" src="/assets/images/lockme_symbol_password.png"></div>';
    } else {
        echo '<div id="time-left" class="time-left" style="display:none"></div>';
        echo '<div id="open-time" class="protection-level-timer" style="display:none"></div>';
        echo '<div id="password-symbol" class="protection-level-password" style="display:none"></div>';
    }

    include __DIR__ . '/templates/box_control_form.php';
}
echo '</div>'; // box-control-area

echo '</div>'; // card-content
echo '</div>'; // overlay-card

echo '<div class="card-footer">';
if ($proVersion == "1") {
    echo "LockMeBox Pro";
}
echo '</div>';
?>