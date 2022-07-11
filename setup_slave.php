<?php

// welcome slave
$mymaster = $_SESSION["mymaster"];
echo "My Master: $mymaster";

// next opening
echo "<br>";
echo "My next opening: ";

// check for master-requests
 // Prepare a select statement
 $sql = "SELECT sender FROM messages WHERE receiver = ? AND messagetype = ?";

 if($stmt = mysqli_prepare($link, $sql)){
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

        // Processing form data when form is submitted
        if($_SERVER["REQUEST_METHOD"] == "POST"){
           

            if (isset($_POST['acceptMasterRequest'])) {

                $mastername = $sender;
                $slavename = $_SESSION["username"];

                $sql = "UPDATE users SET mymaster='$mastername' WHERE username='$slavename'";
                

                if (mysqli_query($link, $sql)) {
                    echo "You are now slave of Master $mastername.";
                    $sql = "UPDATE users SET myslave='$slavename' WHERE username='$mastername'";
                    mysqli_query($link, $sql);
                    $sql = "DELETE FROM messages WHERE receiver='$receiver'";
                    mysqli_query($link, $sql);
                  } 

            }
            if (isset($_POST['declineMasterRequest'])) {
                echo "decline";
               
            }
            
        }  else{
            // show form if not submitted
            echo "Master $sender wants to add you as slave.";  
            require_once "setup_slave_form.php";
        }
      

    }
    
 }                    


?>