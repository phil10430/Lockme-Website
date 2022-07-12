<?php
require_once "config.php";

$UserName = $_POST["UserName"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {    
        $ConStatus = test_input($_POST["ConStatus"]);        
        $UserName = test_input($_POST["UserName"]);   
          
        $BoxName = test_input($_POST["BoxName"]);  
        $LockStatus = test_input($_POST["LockStatus"]);
        $Protection = test_input($_POST["Protection"]);
        $OpenTime = test_input($_POST["OpenTime"]);    

        $sql = "INSERT INTO history (BoxName, LockStatus, Protection, OpenTime)
        VALUES ('" . $BoxName . "', '" . $LockStatus . "', '" . $Protection . "', '" . $OpenTime . "')";

        $sql = "UPDATE users SET conStatus=$ConStatus,
         LockStatus='$LockStatus', Protection = '$Protection',
         OpenTime = '$OpenTime', BoxName = '$BoxName'  WHERE username='$UserName'";

        //optional post item
        if( isset( $_POST['WishedAction'] )) {
            $WishedAction = test_input($_POST["WishedAction"]);     
            $sql = "UPDATE users SET WishedAction='$WishedAction' WHERE username='$UserName'";
        }

        if ($link->query($sql) === TRUE) {
           // echo "New record created successfully";


            $query = "SELECT WishedAction FROM users WHERE username = '$UserName'";
            $result = mysqli_query($link, $query);
            $number_of_rows = mysqli_num_rows($result);
            $response = array();
            if($number_of_rows > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    $response[] = $row;
                }
            }
          //  header('Content-Type: application/json');
            echo json_encode(array("users"=>$response));


        } 
        else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
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