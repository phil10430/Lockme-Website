<?php
echo "Logged in as slave";

// check for master-requests
 // Prepare a select statement
 $sql_checkmessage = "SELECT sender FROM messages WHERE receiver = ? AND messagetype = ?";

 if($stmt = mysqli_prepare($link, $sql_checkmessage)){
     mysqli_stmt_bind_param($stmt, "ss", $receiver, $messagetype);

     $messagetype = "addslave";
     $receiver =  $_SESSION["username"];
    
     mysqli_stmt_execute($stmt);
     mysqli_stmt_store_result($stmt);

     if(mysqli_stmt_num_rows($stmt) == 1)
     {
        mysqli_stmt_bind_result($stmt, $sender);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        echo "Master $sender wants to add you ($receiver) as slave.";    
     }
 }                    


// add next opening
?>