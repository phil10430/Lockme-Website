<?php

// check for master-requests
 // Prepare a select statement
 $sql_checkmessage = "SELECT sender FROM messages WHERE receiver = ? AND messagetype = ?";

 if($stmt = mysqli_prepare($link, $sql_checkmessage)){
     mysqli_stmt_bind_param($stmt, "ss", $receiver, $messagetype);

     $messagetype = "addslave";
     $receiver =  $_SESSION["username"];
    
     mysqli_stmt_execute($stmt);
     mysqli_stmt_store_result($stmt);


     if(mysqli_stmt_num_rows($stmt) > 0)
     {
        mysqli_stmt_bind_result($stmt, $sender);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        echo "Master $sender wants to add you as slave.";   

        
        require_once "setup_slave_form.php";
        // Processing form data when form is submitted
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            if (isset($_POST['acceptMasterRequest'])) {
                echo "accept";

                $sql = "UPDATE users SET mymaster = $sender WHERE username = '$_SESSION["username"]'";

                if (mysqli_query($link, $sql)) {
                    echo "Status updated successfully";
                  } else {
                    echo "Error updating status";
                  }

            }
            if (isset($_POST['declineMasterRequest'])) {
                echo "decline";
               
            }
        }  
    }
 }                    


// add next opening
?>