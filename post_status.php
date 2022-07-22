<?php
require_once "config.php";

//$UserName = $_POST["UserName"];

const REQUEST_TYPE_DEFAULT = "0";
const REQUEST_UPDATE_HISTORY = "1";
const REQUEST_CLEAR_WISHED_ACTION = "2";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
        // get posted variables from APP
        $RequestType = test_input($_POST["RequestType"]); 
        $ConStatus = test_input($_POST["ConStatus"]);        
        $UserName = test_input($_POST["UserName"]);   
        $Password = test_input($_POST["Password"]);
        $BoxName = test_input($_POST["BoxName"]);  
        $LockStatus = test_input($_POST["LockStatus"]);
        $ProtectionLevelTimer = test_input($_POST["ProtectionLevelTimer"]);
        $ProtectionLevelPassword = test_input($_POST["ProtectionLevelPassword"]);
        $OpenTime = test_input($_POST["OpenTime"]);    
        
        // general status update
        $sql = "UPDATE users SET conStatus=$ConStatus,
        LockStatus='$LockStatus', ProtectionLevelTimer = '$ProtectionLevelTimer', ProtectionLevelPassword = '$ProtectionLevelPassword',
        OpenTime = '$OpenTime', BoxName = '$BoxName'  WHERE username='$UserName'";
        $link->query($sql);

        // if lockstatus has changed or open time was extended update history table
        if ($RequestType == REQUEST_UPDATE_HISTORY){
            $sql = "INSERT INTO history (BoxName, LockStatus, ProtectionLevelTimer, ProtectionLevelPassword, OpenTime, username)
            VALUES ('" . $BoxName . "', '" . $LockStatus . "', '" . $ProtectionLevelTimer . "', 
            '" . $ProtectionLevelPassword . "', '" . $OpenTime . "', '" . $UserName . "')";

            $link->query($sql);
        }
        elseif ($RequestType == REQUEST_CLEAR_WISHED_ACTION) {
            $sql = "UPDATE users SET WishedAction='' WHERE username='$UserName'";
            $link->query($sql);
        }
        elseif ($RequestType == REQUEST_TYPE_LOGIN) {
            
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