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


if( !empty($openTime  )){
  $openTime   = date("y-m-d H:i", strtotime("$openTime  "));
}

?>


<img class="center-block" style="height:200px;width:auto;"

<?php
if(($appLoggedIn==1) && ($conStatus==1) && ($appActive == 1)){
  if ($lockStatus == 0) {

    ?>  src="/pictures/lockme_symbol_open.png"><?php

  }elseif ($lockStatus == 1) {
    if (($protectionLevelTimer   == 1) && ($protectionLevelPassword   == 1)) {

        ?> src="/pictures/lockme_symbol_closed_timer_password.png" > <?php
        ?><p class="text-center"> <?php echo $openTime  ; ?> </p> <?php

    } elseif ($protectionLevelTimer   == 1) {

      ?> src="/pictures/lockme_symbol_closed_timer.png">  <?php
      ?><p class="text-center"> <?php echo $openTime  ; ?> </p> <?php

    } elseif ($protectionLevelPassword   == 1) {

      ?> src="/pictures/lockme_symbol_closed_password.png"> <?php

    }  else{

    ?> src="/pictures/lockme_symbol_closed.png"> <?php

    }
  }
}else {

  ?>  src="/pictures/lockme_symbol_unclear.png"> <?php

}
      
    
$connectionStatusMessage = "";
if ($appActive == 1){
    if ($appLoggedIn == 1) { 
            if ($conStatus == 1) {
                $connectionStatusMessage =  "Connected to LockMe-Box #" . $boxName;
            } else {
                $connectionStatusMessage = "App not connected to LockMe-Box. Connect App to LockMe-Box to enable control.";
            }
    }  else{
        $connectionStatusMessage = "App is not connected to Account. Open your App and login.";
    }
} else {
    $connectionStatusMessage = "App is not active. Please open App to enable control.";
}

echo '<br><div class="alert alert-info">' . $connectionStatusMessage . '</div>';

?>
 