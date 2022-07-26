<?php

$name = $_SESSION["username"];
$sql = "SELECT id, BoxName, LockStatus, ProtectionLevelTimer, ProtectionLevelPassword, OpenTime, reading_time 
               FROM history  WHERE username = '$name' ORDER BY id DESC LIMIT 10";
?>
<table class="table table-sm">
  <thead>
    <tr>
      <th scope="col">Box-ID</th>
      <th scope="col">Timestamp</th>
      <th scope="col">Status</th>
      <th scope="col">Protection</th>
      <th scope="col">OpenTime</th>
    </tr>
  </thead>
  <tbody>

    <?php
    $rowCounter = 0;
    if ($result = $link->query($sql)) {
      while (($row = $result->fetch_assoc())) {
      
        $row_id = $row["id"];
        $BoxName = $row["BoxName"];
        $LockStatus = $row["LockStatus"];
        $ProtectionLevelTimer = $row["ProtectionLevelTimer"];
        $ProtectionLevelPassword = $row["ProtectionLevelPassword"];
        $OpenTime = $row["OpenTime"];
        $row_reading_time = $row["reading_time"];

        $row_reading_time = date("d.m. H:i", strtotime("$row_reading_time"));
        
        if($LockStatus==1){
          $LockStatus_String = "closed";
        }else{
          $LockStatus_String = "open";
        }

        $ProtectionLevel = "";
        if (($ProtectionLevelTimer == 1) && ($ProtectionLevelPassword == 1)) {
          $ProtectionLevel = "Timer/Password";
        } elseif ($ProtectionLevelTimer == 1) {
          $ProtectionLevel = "Timer";
        } elseif ($ProtectionLevelPassword == 1) {
          $ProtectionLevel = "Password";
        } else {
          $ProtectionLevel = "None";
        }

    ?>

        <tr>
          <td><?php echo '#'.$BoxName ?></td>
          <td><?php echo $row_reading_time ?></td>
          <td><?php echo $LockStatus_String ?></td>
          <td><?php echo $ProtectionLevel ?></td>
          <td><?php echo $OpenTime ?></td>
        </tr>


    <?php

      }
      $result->free();
    }
    ?>

  </tbody>
</table>