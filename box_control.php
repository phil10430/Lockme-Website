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
            if (isset($_POST['CloseBox'])) {
                if($_POST['protectionLevelRadioGroup']=='timeRadio'){
                    $OpenTime = test_input($_POST["OpenTime"]);
                    $OpenTimeUnix = strtotime($OpenTime); 
                    // command sent to box: C/protectionlevel/password, 
                    $message = MSG_CLOSE.MSG_SEPARATOR.
                                PROTECTION_LEVEL_TIMER.MSG_SEPARATOR.
                                $OpenTimeUnix.MSG_SEPARATOR.
                                "*";
                }
                elseif($_POST['protectionLevelRadioGroup']=='passwordRadio'){
                    $Password = test_input($_POST["Password"]);
                    $message = MSG_CLOSE.MSG_SEPARATOR.
                                PROTECTION_LEVEL_PASSWORD.MSG_SEPARATOR.
                                "*".MSG_SEPARATOR.
                                $Password;
                }
                else{
                    echo "Select Protection Level";
                }
             }
            
            if (isset($_POST['OpenBox'])) {
                $message = MSG_OPEN;
            }
        
            $sql = "UPDATE users SET WishedAction='$message' WHERE username='$name'";
            $link->query($sql);
         } 
     }  
     elseif ($conStatus == CON_STATUS_NOT_CONNECTED){
        echo "not connected - connect to control box";
     }

     function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
      }
   
?>