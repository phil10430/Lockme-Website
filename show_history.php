<?php
$name = $_SESSION["username"];
$sql = "SELECT id, BoxName, LockStatus, ProtectionLevelTimer, 
                ProtectionLevelPassword, OpenTime, reading_time 
               FROM history  WHERE username = '$name' ORDER BY id DESC";
?>
<div class="container">
  <div class="row form-group">
    <div class="col-sm">
      Box Name
    </div>
    <div class="col-sm">
      Lock Status
    </div>
    <div class="col-sm">
      Timer
    </div>
    <div class="col-sm">
      Password
    </div>
    <div class="col-sm">
      Open Time
    </div>
    <div class="col-sm">
      Timestamp
    </div>
  </div>
</div>

<?php

if ($result = $link->query($sql)) {
  while ($row = $result->fetch_assoc()) {
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
    $row_reading_time = date("Y-m-d H:i:s", strtotime("$row_reading_time + 2 hours"));
?>
    <div class="container">
      <div class="row form-group">
        <div class="col-sm">
          <?php echo substr($BoxName, -2) ?>
        </div>
        <div class="col-sm">
          <?php echo $LockStatus ?>
        </div>
        <div class="col-sm">
          <?php echo $ProtectionLevelTimer ?>
        </div>
        <div class="col-sm">
          <?php echo $ProtectionLevelPassword ?>
        </div>
        <div class="col-sm">
          <?php echo $OpenTime ?>
        </div>
        <div class="col-sm">
          <?php echo $row_reading_time ?>
        </div>
      </div>
    </div>

<?php

  }
  $result->free();
}
?>