<?php
session_start();
$username = $_SESSION["username"];

$sql = "SELECT * FROM users WHERE username = :username";
$stmt = $pdo->prepare($sql);
$stmt->execute([':username' => $username]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);


$boxName = $row['box_name'];
$lockStatus = $row['lock_status'];
$protectionLevelTimer   = $row['protection_level_timer'];
$protectionLevelPassword   = $row['protection_level_password'];
$openTime   = $row['open_time'];
$conStatus = $row['con_status'];
$appLoggedIn = $row['app_logged_in'];
$appActive = $row['app_active'];
$lockedSince = $row['locked_since'];
$timeLeft = $row['time_left'];

$closeButtonText = $closeButtonText ?? "CLOSE"; 

/* ----------- debugging ----------------------- */
/*
$lockStatus                   = 1;
$protectionLevelTimer         = 1;
$protectionLevelPassword      = 1;
$openTime                     = "12.11.2025 - 05:30";
$conStatus                    = 1;
$appLoggedIn                  = 1;
$appActive                    = 1;
$lockedSince                  = "since 3d:24h:50m";
$timeLeft                     = "14d left";
*/


if( !empty($openTime  )){
  $openTime   = date("y-m-d H:i", strtotime("$openTime  "));
}

echo '<div class="overlay-card">';


  echo '<img class="bg-image" alt="Background"';
  
  if(($appLoggedIn==1) && ($conStatus==1) && ($appActive == 1)){
    
    if ($lockStatus == 0) {
      $closeButtonText = "CLOSE";  
      echo 'src="/assets/images/icon_box_open.png">';

    }elseif ($lockStatus == 1) 
    {
      $closeButtonText = "OPEN";  
      echo 'src="/assets/images/icon_box_closed.png">';
    }
  }else {
    echo 'src="/assets/images/icon_box_unclear.png">';
  }
  

  echo '<div class="card-content">';   
  
  
    $connectionStatusMessage = "";
    if ($appActive == 1){
        if ($appLoggedIn == 1) { 
                if ($conStatus == 1) {
                    $connectionStatusMessage =  "Box #" . $boxName;
                } else {
                    $connectionStatusMessage = "Connect app to your LOCKMEBOX.";
                }
        }  else{
            $connectionStatusMessage = "Open app and login.";
        }
    } else {
        $connectionStatusMessage = "Open app to enable control.";
    }

    echo '<div class="status-message">' . $connectionStatusMessage . '</div>';

 

    /* show box control form only if status is not unclear */
    if(($appLoggedIn==1) && ($conStatus==1) && ($appActive == 1)){

      if ($lockStatus == 1) {
        /* show locked since */
        echo '<div class="locked-since">'
        . $lockedSince . '<br>'
        .'</div>';
      }

      if (($protectionLevelTimer   == 1) && ($protectionLevelPassword   == 1)) 
      {
      
        echo '<div class="time-left">'
        . $timeLeft
        .'</div>';

        echo '<div class="protection-level-timer">'
        .$openTime
        .'</div>';

        echo '<div class="protection-level-password">'
        .' <img class="password-symbol" src="/assets/images/lockme_symbol_password.png">'
        .'</div>';

      } elseif ($protectionLevelTimer   == 1) 
      {
         echo '<div class="time-left">'
        . $timeLeft
        .'</div>';

        echo '<div class="protection-level-timer">'
        .$openTime
        .'</div>';
      } 
      elseif ($protectionLevelPassword   == 1) 
      {
        echo '<div class="protection-level-password">'
        .' <img class="password-symbol" src="/assets/images/lockme_symbol_password.png">'
        .'</div>';
      }  
      include __DIR__ . '/templates/box_control_form.php';
    }
    
  echo '</div>';

 

echo '</div>';

?>
 