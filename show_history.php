<?php

const STATUS_TIMER_LOCKED = 2;

 // attention!!!!!!! include helper_functions.php does not work !!!!!!!!
$query =  "SELECT id, BoxName, LockStatus, ProtectionLevelTimer, ProtectionLevelPassword, OpenTime, reading_time 
FROM history  WHERE username = ? ORDER BY id DESC LIMIT 20";

$stmt = $link->prepare($query);
$stmt->bind_param("s", $_SESSION["username"]);
$stmt->execute();
$result = $stmt->get_result();
 
$timestamp_old = time();  // current unix time
$i = 0; // loopCounter

$statusArray = array();
$timeStampArray = array();
$openTimeArray = array();
$ProtectionLevelTimer = array();

while ($row = $result->fetch_assoc()) {
    // loop begins with newest entry
    $statusArray[] = $row["LockStatus"];
    $timeStampArray[] = strtotime( $row["reading_time"]);
    $ProtectionLevelTimer[] = $row["ProtectionLevelTimer"];
    $openTimeArray[]  = strtotime($row["OpenTime"]);
}

$nrOfDays = 14;
// generate start date 
 
  // loop through days
for ($i = 0; $i <= $nrOfDays; $i++) 
{
    $day = strtotime("-$i Days", strtotime("Today")); // beginning of day timestamp
    $day_after = $day + 86400; // end of day timestamp

    $dayDistribution = array();
    $dayDistributionStatus = array();

    // loop through timestamp array - start with oldest date (last element of array)
    for($j = count($timeStampArray); $j >= 0; $j--)
    {
        // check if there is a timestamp entry in the current day
        if (($timeStampArray[$j] > $day ) && ($timeStampArray[$j] < $day_after )){
            //append timestamp to day array
            array_push($dayDistribution, $timeStampArray[$j] - $day -  array_sum($dayDistribution));
            array_push($dayDistributionStatus,  $statusArray[$j+1]);
            $status = $statusArray[$j];
        } elseif ($timeStampArray[$j] < $day )
        {
            $status = $statusArray[$j];
        }
    }

    array_push($dayDistributionStatus, $status);
    if($i > 0){
        //add remaining seconds to full day as last entry
        array_push($dayDistribution, 86400 - array_sum($dayDistribution));    
    } else{
        // newest entry until now
        array_push($dayDistribution, time() - array_sum($dayDistribution)- $day);   
        
        // display forecast
        if ($ProtectionLevelTimer[0] == 1){
            array_push($dayDistributionStatus, STATUS_TIMER_LOCKED);
            array_push($dayDistribution, min($day_after, $openTimeArray[0]) - time());   
        }

    }

 
    echo date("D", $day).": ";
    echo date("y-m-d", $day) ;
      
    /*
    echo "<br>";
   for($z =0; $z< count($dayDistribution); $z++)
    {
       $percentage = round($dayDistribution[$z]/(3600*24)*100,2);
       echo $percentage."/".$dayDistributionStatus[$z]."\n";
    } 
    echo "<br><br>";
    */
    

    ?> <div class="progress"><?php

    for($z =0; $z< count($dayDistribution); $z++)
    {
        $percentage = round($dayDistribution[$z]/(3600*24)*100,2);
        
        if ($dayDistributionStatus[$z] === 1){
            ?> <div class="progress-bar bg-danger" role="progressbar" style="width:<?php echo $percentage?>%" aria-valuenow="<?php echo $percentage?>" aria-valuemin="0" aria-valuemax="100">Locked</div><?php
        } elseif ($dayDistributionStatus[$z] === 0){
            ?> <div class="progress-bar bg-success" role="progressbar" style="width:<?php echo $percentage?>%" aria-valuenow="<?php echo $percentage?>" aria-valuemin="0" aria-valuemax="100">Open</div><?php
        } elseif ($dayDistributionStatus[$z] === STATUS_TIMER_LOCKED){
            ?> <div class="progress-bar progress-bar-striped active bg-danger" role="progressbar" style="width:<?php echo $percentage?>%" aria-valuenow="<?php echo $percentage?>" aria-valuemin="0" aria-valuemax="100">Locked</div><?php
        }  
        else {
            ?> <div class="progress-bar bg-warning" role="progressbar" style="width:<?php echo $percentage?>%" aria-valuenow="<?php echo $percentage?>" aria-valuemin="0" aria-valuemax="100">?</div><?php
        } 
    } 

    ?> </div><?php
    unset ($dayDistribution);
    unset ($dayDistributionStatus);
}
 
?>