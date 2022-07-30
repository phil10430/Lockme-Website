<?php
 // attention!!!!!!! include helper_functions.php does not work !!!!!!!!
$query =  "SELECT id, BoxName, LockStatus, ProtectionLevelTimer, ProtectionLevelPassword, OpenTime 
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
  
      $OpenTime = $row["OpenTime"]; 
 
      if( !empty($OpenTime)){
        $OpenTime = date("d.m. H:i", strtotime("$OpenTime"));
      }
    ?>


      
          <?php
            if ($LockStatus == 0) {
              ?>  <img class="center-block" style="height:200px;width:auto;" src="/pictures/lockme_symbol_open.png">   <?php
            } elseif ($LockStatus == 1) {
                if (($ProtectionLevelTimer == 1) && ($ProtectionLevelPassword == 1)) {
                    ?> <img  class="center-block" style="height:200px;width:auto;" src="/pictures/lockme_symbol_closed_timer_password.png"> <?php
                    ?>OpenTime: <?php echo $OpenTime;
                   } elseif ($ProtectionLevelTimer == 1) {
                     ?> <div> <img  class="center-block" style="height:200px;width:auto;" src="/pictures/lockme_symbol_closed_timer.png"></div> <?php
                     ?><p class="text-center"> OpenTime: <?php echo $OpenTime; ?> </p> <?php
                   } elseif ($ProtectionLevelPassword == 1) {
                     ?> <img  class="center-block" style="height:200px;width:auto;" src="/pictures/lockme_symbol_closed_password.png"> <?php
                   }  else{
                    ?> <img   class="center-block" style="height:200px;width:auto;" src="/pictures/lockme_symbol_closed.png"> <?php
                }

            }
         

    }

    ?>
 