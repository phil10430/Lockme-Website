<?php
$query =  "SELECT id, BoxName, LockStatus, ProtectionLevelTimer, ProtectionLevelPassword, OpenTime, reading_time 
FROM history  WHERE username = ? ORDER BY id DESC LIMIT 10";

$stmt = $link->prepare($query);
$stmt->bind_param("s", $_SESSION["username"]);
$stmt->execute();
$result = $stmt->get_result();

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


    while ($row = $result->fetch_assoc()) {

      $row_id = $row["id"];
      $BoxName = $row["BoxName"];
      $LockStatus = $row["LockStatus"];
      $ProtectionLevelTimer = $row["ProtectionLevelTimer"];
      $ProtectionLevelPassword = $row["ProtectionLevelPassword"];
      $OpenTime = $row["OpenTime"];
      $row_reading_time = $row["reading_time"];

      $row_reading_time = date("m-d H:i", strtotime("$row_reading_time"));

      if ($LockStatus == 1) {
        $LockStatus_String = "closed";
      } else {
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
        <td><?php echo '#' . $BoxName ?></td>
        <td><?php echo $row_reading_time ?></td>
        <td><?php echo $LockStatus_String ?></td>
        <td><?php echo $ProtectionLevel ?></td>
        <td><?php echo $OpenTime ?></td>
      </tr>


    <?php

    }

    ?>

  </tbody>
</table>