<?php
    require_once "config.php";
   
    const MSG_OPEN = "O";
    const MSG_CLOSE = "C"; 
    const MSG_SET_OPEN_TIME = "Q";
    const MSG_SET_PASSWORD = "N"; 

    const MSG_SEPARATOR = "/";

    const PROTECTION_LEVEL_NONE = "0";
    const PROTECTION_LEVEL_TIMER = "1";
    const PROTECTION_LEVEL_PASSWORD = "2";
    const PROTECTION_LEVEL_TIMER_OR_PASSWORD = "3";

    const CON_STATUS_NOT_CONNECTED = 0;
    const CON_STATUS_CONNECTED = 1;

    $name = $_SESSION["username"]; 

     // get conStatus from Database
     $result = mysqli_query($link,"SELECT conStatus FROM users WHERE username = '$name'");
     while($row = mysqli_fetch_array($result))
     $conStatus =  $row['conStatus'];
    
    
     if ($conStatus == CON_STATUS_CONNECTED){
        require "box_control_form.php";
        echo "Status: connected";
         // Processing form data when form is submitted
       
         if($_SERVER["REQUEST_METHOD"] == "POST"){
            $OpenTime = test_input($_POST["OpenTime"]);
            $dt = DateTime::createFromFormat("d/m/Y H:i", $OpenTime);
            $OpenTimeUnix = $dt->getTimestamp();
            $Password = test_input($_POST["Password"]);
            if (isset($_POST['CloseBox'])) {
                if (isset($_POST['timeCheckbox']) && isset($_POST['passwordCheckbox'])){
                    $message = MSG_CLOSE.MSG_SEPARATOR.
                    PROTECTION_LEVEL_TIMER_OR_PASSWORD.MSG_SEPARATOR.
                    $OpenTimeUnix.MSG_SEPARATOR.
                    $Password;
                }
                elseif(isset($_POST['timeCheckbox'])){
                    // command sent to box: C/protectionlevel/password, 
                    $message = MSG_CLOSE.MSG_SEPARATOR.
                                PROTECTION_LEVEL_TIMER.MSG_SEPARATOR.
                                $OpenTimeUnix.MSG_SEPARATOR.
                                "*";
                }
                elseif(isset($_POST['passwordCheckbox'])){
                    $message = MSG_CLOSE.MSG_SEPARATOR.
                                PROTECTION_LEVEL_PASSWORD.MSG_SEPARATOR.
                                "*".MSG_SEPARATOR.
                                $Password;
                }
                else{
                    $message = MSG_CLOSE.MSG_SEPARATOR.
                                PROTECTION_LEVEL_NONE.MSG_SEPARATOR.
                                "*".MSG_SEPARATOR.
                                "*";
                }
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
        echo "Status: not connected";
     }

     function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
      }
   
?>