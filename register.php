<?php
include 'helper_functions.php';

// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$email = "";
$email_err = "";
$username_err = $password_err = $confirm_password_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } elseif(!isValidUsername(trim($_POST["username"]))){
        $username_err = "Username can only contain letters and numbers.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
         
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = mysqli_real_escape_string($link,trim($_POST["username"]));
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have atleast 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
  

    // Validate email
    $email_temp = trim($_POST["email"]);

    if(empty($email_temp)){
        $email_err = "Please enter a Email.";     
    } elseif(strlen($email_temp) < 6){
        $email_err = "email must have at least 6 characters.";
    } elseif(!isValidEmail($email_temp)){
        $email_err = "email has wrong format.";
    }  else{
        $sql = "SELECT id FROM users WHERE email = ?";


        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $email_temp);
        
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "This email is already taken.";
                } else{
                    $email = mysqli_real_escape_string($link,$email_temp);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        } 
    }

    // Check input errors before inserting in database
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err) &&  empty($email_err) ){
        
        // Prepare an insert statement
        $sql = "INSERT INTO users (username, password, email) VALUES (?,?,?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            // sss = number of columns 
            mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_password, $email);
            
            // Set parameters
            $param_username = $username;
            $param_email = $email;
            $password = mysqli_real_escape_string($link,$password);
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
          
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                 // Password is correct, so start a new session
                 session_start();
                 // Store data in session variables
                 $_SESSION["loggedin"] = true;
                 $_SESSION["id"] = $id;
                 $_SESSION["username"] = $username;                            
                 $_SESSION["email"] = $email;    
                 // Redirect user to welcome page
                  header("location: index.php");
            } else{
                // uncomment following line to see error message
                // echo mysqli_error($link);
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    
    
    // Close connection
    mysqli_close($link);
}
?>