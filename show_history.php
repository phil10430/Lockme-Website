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


  
    $timestamp_old = time();  // current unix time
    $i = 0; // loopCounter

    while ($row = $result->fetch_assoc()) {
      $row_id = $row["id"];
      $BoxName = $row["BoxName"];
      $LockStatus = $row["LockStatus"];
      $ProtectionLevelTimer = $row["ProtectionLevelTimer"];
      $ProtectionLevelPassword = $row["ProtectionLevelPassword"];
  
      $OpenTime = $row["OpenTime"];
      $row_reading_time = $row["reading_time"];

      $timestamp = strtotime("$row_reading_time");
      $row_reading_time = date("d.m. H:i", $timestamp);
      $day = date("d", $timestamp);

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
           ?> <span class="glyphicon glyphicon-time"></span> / <span class="glyphicon glyphicon-option-horizontal"></span>  <?php
          } elseif ($ProtectionLevelTimer == 1) {
            ?> <span class="glyphicon glyphicon-time"></span>  <?php
          } elseif ($ProtectionLevelPassword == 1) {
            ?> <span class="glyphicon glyphicon-option-horizontal"></span>  <?php
          } 
          ?> 
        </td>
        <td><?php echo $OpenTime ?></td>
      </tr>
      

      <?php
        // loop begins with newest entry
        $timeStampArray[$i] = $timestamp;
        $statusArray[$i]  = $LockStatus;
        $i = $i + 1;
      ?>

   
      

    <?php

    }

    ?>

  </tbody>
</table>


<?php

$nrOfDays = 8;
// generate start date 
$enddate = strtotime("Today");
$startdate = strtotime("-$nrOfDays Days", $enddate);
 
 
  // loop through days
  for ($i = 0; $i <= $nrOfDays; $i++) 
  {
  //  $enddate = strtotime("Today");
    $day = strtotime("+$i Days", $startdate); // beginning of day timestamp
    $day_after = strtotime("+1 Days", $day); // end of day timestamp

    $dayDistribution = array();
    $dayDistributionStatus = array();

    // loop through timestamp array - start with oldest date (last element of array)
    $j = count($timeStampArray);
    while($j >= 0)
    {
        // check if there is a timestamp entry in the current day
        if (($timeStampArray[$j] > $day ) && ($timeStampArray[$j] < $day_after )){
            //append timestamp to day array
            array_push($dayDistribution, $timeStampArray[$j] - $day -  array_sum($dayDistribution));
            array_push($dayDistributionStatus, $status_old);
            $status_old = $statusArray[$j];
        }
        $j--;
    }
    array_push($dayDistributionStatus, $status_old);
    //add remaining seconds to full day as last entry
    array_push($dayDistribution, 3600*24 - array_sum($dayDistribution)); 

    foreach ($dayDistribution as $value){
      //  echo $value."\n";
    }
    
    $z = 0;

    
    echo date("Y-m-d", $day) ;
    /*
    echo "<br>";

    while($z < count($dayDistribution) )
    {
       $percentage = round($dayDistribution[$z]/(3600*24)*100,2);
       echo $percentage."/".$dayDistributionStatus[$z]."\n";
       $z = $z+1; 
    } 
    echo "<br><br>";
    */

    ?> <div class="progress"><?php

    while($z < count($dayDistribution) )
    {

        $percentage = round($dayDistribution[$z]/(3600*24)*100,2);
        
        if ($dayDistributionStatus[$z] == 1){
            ?> <div class="progress-bar bg-danger" role="progressbar" style="width:<?php echo $percentage?>%" aria-valuenow="<?php echo $percentage?>" aria-valuemin="0" aria-valuemax="100">Locked</div><?php
        } else{
            ?> <div class="progress-bar bg-success" role="progressbar" style="width:<?php echo $percentage?>%" aria-valuenow="<?php echo $percentage?>" aria-valuemin="0" aria-valuemax="100">Open</div><?php
        }
        $z = $z+1;
    } 

    ?> </div><?php

    
    unset ($dayDistribution);
    unset ($dayDistributionStatus);
 
}
 
?>