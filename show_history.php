<!DOCTYPE html>
<html><body>
<?php
$name = $_SESSION["username"]; 
$sql = "SELECT id, BoxName, LockStatus, ProtectionLevelTimer, 
                ProtectionLevelPassword, OpenTime, reading_time 
               FROM history  WHERE username = '$name' ORDER BY id DESC";

echo '<table cellspacing="5" cellpadding="5">
      <tr> 
        <td>BoxName</td> 
        <td>LockStatus</td> 
        <td>Timer</td> 
        <td>Password</td> 
        <td>OpenTime</td>
        <td>Timestamp</td> 
      </tr>';

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
      
        echo '<tr> 
                <td>' . substr( $BoxName , -2). '</td> 
                <td>' . $LockStatus . '</td> 
                <td>' . $ProtectionLevelTimer . '</td> 
                <td>' . $ProtectionLevelPassword . '</td> 
                <td>' . $OpenTime . '</td> 
                <td>' . $row_reading_time . '</td> 
              </tr>';
    }
    $result->free();
}
?> 
</table>
</body>
</html>