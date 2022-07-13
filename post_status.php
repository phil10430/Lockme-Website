<?php
require_once "config.php";

$UserName = $_POST["UserName"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
        // get posted variables from APP
        $RequestType = test_input($_POST["RequestType"]);  //0: default,  1: update history, 2: clear wished action
        $ConStatus = test_input($_POST["ConStatus"]);        
        $UserName = test_input($_POST["UserName"]);   
        $BoxName = test_input($_POST["BoxName"]);  
        $LockStatus = test_input($_POST["LockStatus"]);
        $ProtectionLevel = test_input($_POST["ProtectionLevel"]);
        $OpenTime = test_input($_POST["OpenTime"]);    
        
        // general status update
        $sql = "UPDATE users SET conStatus=$ConStatus,
        LockStatus='$LockStatus', ProtectionLevel = '$ProtectionLevel',
        OpenTime = '$OpenTime', BoxName = '$BoxName'  WHERE username='$UserName'";
        $link->query($sql);

        // if lockstatus has changed update history table
        if ($RequestType == 1){
            $sql = "INSERT INTO history (BoxName, LockStatus, ProtectionLevel, OpenTime)
            VALUES ('" . $BoxName . "', '" . $LockStatus . "', '" . $ProtectionLevel . "', '" . $OpenTime . "')";
            $link->query($sql);
        }
        elseif ($RequestType == 2) {
            $sql = "UPDATE users SET WishedAction='' WHERE username='$UserName'";
            $link->query($sql);
        }
        
        // send back WishedAction to APP
        $query = "SELECT WishedAction FROM users WHERE username = '$UserName'";
        $result = mysqli_query($link, $query);
        $number_of_rows = mysqli_num_rows($result);
        $response = array();
        if($number_of_rows > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $response[] = $row;
            }
        }
        echo json_encode(array("users"=>$response));
      
        
}
else {
    echo "No data posted with HTTP POST.";
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


    
?>