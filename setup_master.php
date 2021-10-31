<?php
// Define variables and initialize with empty values
$slavename  = "";
$role = "";
$slavename_err  = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if (isset($_POST['submitRequestData'])) {

        // Check if username is empty
        if(empty(trim($_POST["slavename"]))){
            $slavename_err = "Please enter slaves name.";
        } else{
            $slavename = trim($_POST["slavename"]);
            $role = trim($_POST["role"]);
        }
        
        // Validate credentials
        if(empty($slavename_err)){

            // Prepare a select statement
            $sql = "SELECT id, username, role, email FROM users WHERE username = ?";
            

            if($stmt = mysqli_prepare($link, $sql)){

                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_slavename);
                
                // Set parameters
                $param_slavename = $slavename;

                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    // Store result
                    mysqli_stmt_store_result($stmt);
                    
                    // Check if username exists
                    if(mysqli_stmt_num_rows($stmt) == 1){                    
                        // Bind result variables
                        mysqli_stmt_bind_result($stmt, $id, $slavename, $role, $email);

                        if(mysqli_stmt_fetch($stmt)){
                            if ($role == "Slave"){
                                // send request to slave
                               
                                require_once "addslave.php";
                                
                            }
                            else {
                                $login_err = "User $slavename is not registered as Slave";
                            }
                        }

                    } else{
                        // Username doesn't exist, display a generic error message
                        $login_err = "Slave $slavename does not exist";
                    }
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }
            

                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
        
        // Close connection
        mysqli_close($link);
    }
}
?>