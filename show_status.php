<?php

$query =  "SELECT id, BoxName, LockStatus, ProtectionLevelTimer, ProtectionLevelPassword, OpenTime, conStatus, appLoggedIn
FROM users  WHERE username = ?";

$stmt = $link->prepare($query);
$stmt->bind_param("s", $_SESSION["username"]);
$stmt->execute();
$result = $stmt->get_result();

$rowCounter = 0;

while ($row = $result->fetch_assoc()) {
  $row_id = $row["id"];
  $BoxName = $row["BoxName"];
  $LockStatus = $row["LockStatus"];
  $ProtectionLevelTimer = $row["ProtectionLevelTimer"];
  $ProtectionLevelPassword = $row["ProtectionLevelPassword"];
  $conStatus = $row["conStatus"];
  $appLoggedIn = $row["appLoggedIn"];
  $OpenTime = $row["OpenTime"]; 

  if( !empty($OpenTime)){
    $OpenTime = date("y-m-d H:i", strtotime("$OpenTime"));
  }
}
?>


<img class="center-block" style="height:200px;width:auto;"

<?php
if(($appLoggedIn==1) && ($conStatus==1)){
  if ($LockStatus == 0) {

    ?>  src="/pictures/lockme_symbol_open.png"><?php

  }elseif ($LockStatus == 1) {
    if (($ProtectionLevelTimer == 1) && ($ProtectionLevelPassword == 1)) {

        ?> src="/pictures/lockme_symbol_closed_timer_password.png" > <?php
        ?><p class="text-center"> <?php echo $OpenTime; ?> </p> <?php

    } elseif ($ProtectionLevelTimer == 1) {

      ?> src="/pictures/lockme_symbol_closed_timer.png">  <?php
      ?><p class="text-center"> <?php echo $OpenTime; ?> </p> <?php

    } elseif ($ProtectionLevelPassword == 1) {

      ?> src="/pictures/lockme_symbol_closed_password.png"> <?php

    }  else{

    ?> src="/pictures/lockme_symbol_closed.png"> <?php

    }
  }
}else {

  ?>  src="/pictures/lockme_symbol_unclear.png"> <?php

}
      
    

?>
 