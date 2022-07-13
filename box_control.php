<?php
    require_once "config.php";
   
    const MSG_OPEN = "O";
    const MSG_CLOSE = "C"; 
    const MSG_SET_OPEN_TIME = "Q";
    const SG_SET_PASSWORD = "N"; 

    const PROTECTION_LEVEL_NONE = "0";
    const PROTECTION_LEVEL_TIMER = "1";
    const PROTECTION_LEVEL_PASSWORD = "2";

    const CON_STATUS_NOT_CONNECTED = 0;
    const CON_STATUS_CONNECTED = 1;

    $name = $_SESSION["username"]; 

     // get conStatus from Database
     $result = mysqli_query($link,"SELECT conStatus FROM users WHERE username = '$name'");
     while($row = mysqli_fetch_array($result))
     $conStatus =  $row['conStatus'];

    
     if ($conStatus == CON_STATUS_CONNECTED){
        require "box_control_form.php";
        echo "connected";
         // Processing form data when form is submitted
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            if (isset($_POST['OpenBox'])) {
                $message = MSG_OPEN;
            }
            else if (isset($_POST['CloseBox'])) {
                $message = MSG_CLOSE;
            }
            $sql = "UPDATE users SET WishedAction='$message' WHERE username='$name'";
            $link->query($sql);
         } 
     }  
     elseif ($conStatus == CON_STATUS_NOT_CONNECTED){
        echo "not connected - connect to control box";
     }


   
?>