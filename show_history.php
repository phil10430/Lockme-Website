<?php
 // attention!!!!!!! include helper_functions.php does not work !!!!!!!!
$query =  "SELECT id, BoxName, LockStatus, ProtectionLevelTimer, ProtectionLevelPassword, OpenTime, reading_time 
FROM history  WHERE username = ? ORDER BY id DESC LIMIT 20";

$stmt = $link->prepare($query);
$stmt->bind_param("s", $_SESSION["username"]);
$stmt->execute();
$result = $stmt->get_result();

?>
<br>
<table class="table table-condensed">
  <thead>
    <tr>
      <th scope="col">BoxID</th>
      <th scope="col">Timestamp</th>
      <th scope="col">Status</th>
      <th scope="col">Protection</th>
      <th scope="col">OpenTime</th>
    </tr>
  </thead>
  <tbody>

  
    <?php

    while ($row = $result->fetch_assoc()) {

      $row_id = $row["id"];
      $BoxName = $row["BoxName"];
      $LockStatus = $row["LockStatus"];
      $ProtectionLevelTimer = $row["ProtectionLevelTimer"];
      $ProtectionLevelPassword = $row["ProtectionLevelPassword"];
  
      $OpenTime = $row["OpenTime"];
      $row_reading_time = $row["reading_time"];

      $row_reading_time = date("d.m. H:i", strtotime("$row_reading_time"));
    
      if( !empty($OpenTime)){
        $OpenTime = date("d.m. H:i", strtotime("$OpenTime"));
      }

    ?>


      <tr>
        <td><?php echo '#' . $BoxName ?></td>
        <td><?php echo $row_reading_time ?></td>
        <td class="text-center">
          <?php
            if ($LockStatus == 1) {
              ?> <span class="glyphicon glyphicon-lock"></span>  <?php
            }  
           ?>
       </td>
        <td class="text-center">
          <?php 
          if (($ProtectionLevelTimer == 1) && ($ProtectionLevelPassword == 1)) {
           ?> <span class="glyphicon glyphicon-time"></span> / <span class="glyphicon glyphicon-text-color"></span>  <?php
          } elseif ($ProtectionLevelTimer == 1) {
            ?> <span class="glyphicon glyphicon-time"></span>  <?php
          } elseif ($ProtectionLevelPassword == 1) {
            ?> <span class="glyphicon glyphicon-text-color"></span>  <?php
          } 
          ?>
        </td>
        <td><?php echo $OpenTime ?></td>
      </tr>


    <?php

    }

    ?>

  </tbody>
</table>