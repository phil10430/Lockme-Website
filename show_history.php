<?php

const MAX_NR_OF_ROWS = 10; // nr of rows to be displayed

$name = $_SESSION["username"];
$sql = "SELECT id, BoxName, LockStatus, ProtectionLevelTimer, ProtectionLevelPassword, OpenTime, reading_time 
               FROM history  WHERE username = '$name' ORDER BY id DESC";
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
      while (($row = $result->fetch_assoc())&& ($rowCounter < MAX_NR_OF_ROWS)) {
        $rowCounter = $rowCounter + 1;
        $row_id = $row["id"];
        $BoxName = $row["BoxName"];
        $LockStatus = $row["LockStatus"];
        $ProtectionLevelTimer = $row["ProtectionLevelTimer"];
        $ProtectionLevelPassword = $row["ProtectionLevelPassword"];
        $OpenTime = $row["OpenTime"];
        $row_reading_time = $row["reading_time"];

        // Uncomment to set timezone to - 1 hour (you can change 1 to any number)
        // $row_reading_time = date("Y-m-d H:i:s", strtotime("$row_reading_time - 1 hours"));

        // Uncomment to set timezone to + 4 hours (you can change 4 to any number)
        $row_reading_time = date("d.m. H:i", strtotime("$row_reading_time + 2 hours"));

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
          <td><?php echo $LockStatus ?></td>
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