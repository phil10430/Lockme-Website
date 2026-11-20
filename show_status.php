<?php
session_start();

include __DIR__ . '/templates/open_dialog.php';
include __DIR__ . '/templates/lock_dialog.php';
include __DIR__ . '/templates/lock_dialog_random_time.php';

// DEBUG -> coomment out autorefresh in header.php
define('DEBUG_WEBSITE', false);

$username = $_SESSION["username"];

// --- Eingeloggten User laden ---
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute([':username' => $username]);
$me = $stmt->fetch(PDO::FETCH_ASSOC);

// --- Eingeloggten User laden ---
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
$stmt->execute([':username' => $username]);
$me = $stmt->fetch(PDO::FETCH_ASSOC);

$isRemote = false;
$stateRow = $me;
$boxName  = (int)$me['box_id_con'];

// Remote-Prüfung, wenn der User selbst keine gültige Box hat ODER kein Pro ist
if ($boxName <= 0 || $me['pro_version'] != "1") {
    $stmt = $pdo->prepare(
        "SELECT box_id, granted_by
           FROM box_permissions
          WHERE user_id = :uid
            AND (revoked_at IS NULL OR revoked_at > NOW())
          ORDER BY created_at DESC
          LIMIT 1"
    );
    $stmt->execute([':uid' => $me['id']]);
    $perm = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($perm) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $perm['granted_by']]);
        $owner = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($owner) {
            $isRemote = true;
            $stateRow = $owner;
            $boxName  = ((int)$owner['box_id_con'] === (int)$perm['box_id']) ? (int)$perm['box_id'] : 0;
        }
    }
}

$appLoggedIn = $stateRow['app_logged_in'];
$appActive   = $stateRow['app_active'];
$proVersion  = $stateRow['pro_version'];   // Pro-Status des Besitzers zählt

// --- Box-Daten ---
$lockStatus = $protectionLevelTimer = $protectionLevelPassword = 0;
$openTime = $lockedSince = $timeLeft = '';

if (!empty($boxName)) {
    $stmt = $pdo->prepare("SELECT * FROM box_data_actual WHERE box_id = :box_id");
    $stmt->execute([':box_id' => $boxName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $lockStatus              = $row['lock_status'];
        $protectionLevelTimer    = $row['protection_level_timer'];
        $protectionLevelPassword = $row['protection_level_password'];
        $openTime                = $row['open_time'];
        $lockedSince             = $row['locked_since'];
        $timeLeft                = $row['time_left'];
    }
}

if (DEBUG_WEBSITE) {
    $lockStatus = 0;
    $appLoggedIn = 1;
    $appActive = 1;
    $proVersion = "1";
    $protectionLevelPassword = 0;
    $protectionLevelTimer = 0;
    $boxName = 191919;
    $timeLeft = '5h left';
    $lockedSince = 'since 3 day';
    $openTime = '2026/05/03 18:10';
}

if (!empty($openTime)) {
    $openTime = date("Y-m-d H:i", strtotime($openTime));
}

if ($proVersion == "1") {

    echo '<div class="overlay-card">';

    // Hintergrundbild
    echo '<img id="bg-image" class="bg-image" alt="Background" ';
    if (($appLoggedIn == 1) && ($boxName != 0) && ($appActive == 1)) {
        echo ($lockStatus == 0)
            ? 'src="/assets/images/icon_box_open.png">'
            : 'src="/assets/images/icon_box_closed.png">';
    } else {
        echo 'src="/assets/images/lmb_start.png">';
    }

    echo '<div class="card-content">';

    // Status-Nachricht
    if ($appActive == 1) {
        if ($appLoggedIn == 1) {
            if ($boxName != 0) {
                $connectionStatusMessage = "LMB " . $boxName;
            } else {
                $connectionStatusMessage = $isRemote
                    ? "Owner's app is not connected to the LockMeBox."
                    : "Connect app to your LockMeBox.";
            }
        } else {
            $connectionStatusMessage = $isRemote
                ? "Owner is not logged in to the app."
                : "Open app and login.";
        }
    } else {
        $connectionStatusMessage = $isRemote
            ? "Owner's app is not active."
            : "Open app for remote control.";
    }
    echo '<div id="status-message" class="status-message">' . htmlspecialchars($connectionStatusMessage) . '</div>';

    echo '<div id="box-control-area">';

    $ready = ($appLoggedIn == 1 && $boxName != 0 && $appActive == 1);

    $displayForm   = $ready ? '' : 'display:none;';
    $displayLocked = ($ready && $lockStatus == 1) ? '' : 'display:none;';

    $timerActive    = ($ready && $lockStatus == 1 && $protectionLevelTimer == 1);
    $passwordActive = ($ready && $lockStatus == 1 && $protectionLevelPassword == 1);

    $displayTimer   = $timerActive    ? '' : 'display:none;';
    $displayPw      = $passwordActive ? '' : 'display:none;';
    $displayWrapper = ($timerActive || $passwordActive) ? '' : 'display:none;';

    echo '<div id="box-control-form" style="' . $displayForm . '">';
    include __DIR__ . '/templates/button_states.php';
    include __DIR__ . '/templates/box_control_form.php';
    echo '</div>';

    echo '<div id="locked-text" class="locked-text" style="' . $displayLocked . '">LOCKED<br></div>';
    echo '<div id="locked-since" class="locked-since" style="' . $displayLocked . '">' . $lockedSince . '<br></div>';

    echo '<div id="time-left" class="time-left" style="' . $displayTimer . '">' . $timeLeft . '</div>';
    echo '<div id="open-time" class="end-time" style="' . $displayTimer . '">' . $openTime . '</div>';

    echo '<div id="protection-level-wrapper" class="protection-level-wrapper" style="' . $displayWrapper . '">';
    echo '<div id="password-symbol" style="' . $displayPw . '"><img class="password-symbol" src="/assets/images/icon_password_grey.png"></div>';
    echo '<div id="timer-symbol" style="' . $displayTimer . '"><img class="timer-symbol" src="/assets/images/icon_timer_grey.png"></div>';
    echo '</div>';

    echo '</div>'; // box-control-area
    echo '</div>'; // card-content
    echo '</div>'; // overlay-card

} else {

    echo '<div class="overlay-card">';
    echo '<img id="bg-image" class="bg-image" alt="Background" src="/assets/images/lmb_start.png">';
    if ($isRemote) {
        // Remote Controller soll kein Pro-Kaufformular sehen
        echo '<div class="card-content"><div id="status-message" class="status-message">Owner has no Pro version.</div></div>';
    } else {
        include __DIR__ . '/templates/get_pro_form.php';
    }
    echo '</div>';
}

echo '<div class="card-footer"></div>';
?>