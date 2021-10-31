<?php
    $sql_messages = "INSERT INTO messages (sender, receiver, messagetype) VALUES (?,?,?)";

    if($stmt = mysqli_prepare($link, $sql_messages)){
        // Bind variables to the prepared statement as parameters
        // sss = number of columns 
        $messagetype = "addslave";
        mysqli_stmt_bind_param($stmt, "sss", $_SESSION["username"], $slavename, $messagetype);
        mysqli_stmt_execute($stmt);
        echo "Request sent to Slave $slavename";    
    }                    
                
?>