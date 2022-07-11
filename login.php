<?php
// Initialize the session
session_start();
 
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$username = $password = "";
$role = "";
$username_err = $password_err = $login_err = "";

//echo "blabla error $slavename";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if (isset($_POST['submitLogin'])) {

    
        // Check if username is empty
        if(empty(trim($_POST["username"]))){
            $username_err = "Please enter username.";
        } else{
            $username = mysqli_real_escape_string($link,trim($_POST["username"]));
        //   $role = trim($_POST["role"]);

        }
        
        // Check if password is empty
        if(empty(trim($_POST["password"]))){
            $password_err = "Please enter your password.";
        } else{
            $password = mysqli_real_escape_string($link, trim($_POST["password"]));
        }
        
        // Validate credentials
        if(empty($username_err) && empty($password_err)){
            // Prepare a select statement
            $sql = "SELECT id, username,  password, role, mymaster, myslave FROM users WHERE username = ?";
            
            if($stmt = mysqli_prepare($link, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_username);
                
                // Set parameters
                $param_username = $username;
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    // Store result
                    mysqli_stmt_store_result($stmt);
                    
                    // Check if username exists, if yes then verify password
                    // checks if 1 row is returend
                    if(mysqli_stmt_num_rows($stmt) == 1){   

                        // Bind result variables
                        mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role, $mymaster, $myslave);
                        if(mysqli_stmt_fetch($stmt)){
                            if(password_verify($password, $hashed_password)){
                                // Password is correct, so start a new session
                                session_start();
                                
                                // Store data in session variables
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username;        
                                $_SESSION["role"] = $role;                      
                                $_SESSION["mymaster"] = $mymaster;  
                                $_SESSION["myslave"] = $myslave;  
                                
                                // Redirect user to welcome page
                                header("location: index.php");
                            } else{
                                // Password is not valid, display a generic error message
                                $login_err = "Invalid username or password.";
                            }
                        }
                    } else{
                        // Username doesn't exist, display a generic error message
                        $login_err = "Invalid username or password.";
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