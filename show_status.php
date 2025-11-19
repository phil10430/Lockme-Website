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
$closeButtonText = $closeButtonText ?? "Close"; 

if( !empty($openTime  )){
  $openTime   = date("y-m-d H:i", strtotime("$openTime  "));
}

echo '<div class="overlay-card">';

  echo '<img class="bg-image" alt="Background"';

    if(($appLoggedIn==1) && ($conStatus==1) && ($appActive == 1)){
      
      if ($lockStatus == 0) {
        $closeButtonText = "Close";  
        echo 'src="/pictures/icon_box_open.png">';

      }elseif ($lockStatus == 1) 
      {
        $closeButtonText = "Open";  
        echo 'src="/pictures/icon_box_closed.png">';
      }
    }else {
      echo 'src="/pictures/icon_box_unclear.png">';
    }

    echo '<div class="card-content">';   
   
    
      $connectionStatusMessage = "";
      if ($appActive == 1){
          if ($appLoggedIn == 1) { 
                  if ($conStatus == 1) {
                      $connectionStatusMessage =  "Box #" . $boxName;
                  } else {
                      $connectionStatusMessage = "Connect LOCKME app to your box.";
                  }
          }  else{
              $connectionStatusMessage = "Open LOCKME app and login.";
          }
      } else {
          $connectionStatusMessage = "Open LOCKME app to enable control.";
      }

      echo '<div class="status-message">' . $connectionStatusMessage . '</div>';


      /* show box control form only if status is not unclear */
      if(($appLoggedIn==1) && ($conStatus==1) && ($appActive == 1)){

        if (($protectionLevelTimer   == 1) && ($protectionLevelPassword   == 1)) 
        {
          echo '<div class="open-time">'
          . $lockedSince . '<br>'
          . $timeLeft . '<br>'
          . $openTime .  '<br>'
          . "Password" .
          '</div>';
        } elseif ($protectionLevelTimer   == 1) 
        {
          echo '<div class="open-time">'
          . $lockedSince . '<br>'
          . $timeLeft . '<br>'
          . $openTime .  '<br>'
          .
          '</div>';
        } 
        elseif ($protectionLevelPassword   == 1) 
        {
          echo '<div class="open-time">'
          . "Password" .
          '</div>';
        }  
        
          include  "box_control_form.php";

      }
     echo '</div>';

  echo '</div>';

echo '</div>';

?>
 