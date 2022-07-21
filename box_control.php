<?php
    require_once "config.php";
   
    const MSG_OPEN = "O";
    const MSG_CLOSE = "C"; 
    const MSG_SET_OPEN_TIME = "Q";
    const MSG_SET_PASSWORD = "N"; 

    const MSG_SEPARATOR = "/";

    const CON_STATUS_NOT_CONNECTED = 0;
    const CON_STATUS_CONNECTED = 1;

    $boxControlError = "";
    $connectionStatus = "";
    $name = $_SESSION["username"]; 

     // get variables from Database
     $result = mysqli_query($link,"SELECT conStatus, BoxName FROM users WHERE username = '$name'");
    
     while ($row = $result->fetch_assoc()){
        $conStatus =  $row['conStatus'];
        $BoxName =  $row['BoxName'];
     }
    
     if ($conStatus == CON_STATUS_CONNECTED){
        require "box_control_form.php";
        $connectionStatus =  "Connected to ". $BoxName;
         // Processing form data when form is submitted
         if($_SERVER["REQUEST_METHOD"] == "POST"){
            $OpenTimeUnix = "";
            $Password ="";

            if (isset($_POST['CloseBox'])) {
                $plTimer = "0";  
                $plPassword = "0"; 
                if (isset($_POST['timeCheckbox'])) { 
                    $OpenTime = test_input($_POST["OpenTime"]);
                    if (validateDate($OpenTime)){
                        $dt = DateTime::createFromFormat("d/m/Y H:i", $OpenTime);
                        $OpenTimeUnix = $dt->getTimestamp();
                        $plTimer = "1";   
                    }else {
                        $boxControlError = "Invalid date";
                    }
                } 
                if (isset($_POST['passwordCheckbox'])) { 
                    $Password = test_input($_POST["Password"]);
                    $plPassword = "1";   
                } 
                $message = MSG_CLOSE.MSG_SEPARATOR.
                                $plTimer.MSG_SEPARATOR.$plPassword.MSG_SEPARATOR.
                                $OpenTimeUnix.MSG_SEPARATOR.
                                $Password;
             }
            
            if (isset($_POST['OpenBox'])) {
                $message = MSG_OPEN.MSG_SEPARATOR.
                $Password;
            }
        
            $sql = "UPDATE users SET WishedAction='$message' WHERE username='$name'";
            $link->query($sql);
       
         } 
     }  
     elseif ($conStatus == CON_STATUS_NOT_CONNECTED){
        $connectionStatus = "Not connected to LockMe-Box";
     }
   
     function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
      }

      function validateDate($date) {
        $format = 'd/m/Y H:i'; // Eg : 21/07/2022 14:40
        $dateTime = DateTime::createFromFormat($format, $date);
    
        if ($dateTime instanceof DateTime && $dateTime->format('d/m/Y H:i') == $date) {
            return true;
        }
    
        return false;
    }
