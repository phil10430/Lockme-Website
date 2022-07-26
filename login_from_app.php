<?php
// Include config file
require_once "config.php";
include 'helper_functions.php';

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST["RequestType"] !== REQUEST_TYPE_LOGOUT){
        // Check if username is empty
        if (empty(trim($_POST["username"]))) {
            $username_err = "Please enter username.";
        } else {
            $username = mysqli_real_escape_string($link, trim($_POST["username"]));
        }

        // Check if password is empty
        if (empty(trim($_POST["password"]))) {
            $password_err = "Please enter your password.";
        } else {
            $password = mysqli_real_escape_string($link, trim($_POST["password"]));
        }

        // Validate credentials
        if (empty($username_err) && empty($password_err)) {
            // Prepare a select statement
            $sql = "SELECT id, username,  password FROM users WHERE username = ?";

            if ($stmt = mysqli_prepare($link, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $param_username);

                // Set parameters
                $param_username = $username;

                // Attempt to execute the prepared statement
                if (mysqli_stmt_execute($stmt)) {
                    // Store result
                    mysqli_stmt_store_result($stmt);

                    // Check if username exists, if yes then verify password
                    // checks if 1 row is returend
                    if (mysqli_stmt_num_rows($stmt) == 1) {

                        // Bind result variables
                        mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                        if (mysqli_stmt_fetch($stmt)) {
                            if (password_verify($password, $hashed_password)) {
                              $isLoggedIn = 1;
                              $query = "UPDATE users SET appLoggedIn=? WHERE username=?";
                              $stmt = mysqli_prepare($link, $query);
                              mysqli_stmt_bind_param($stmt, 'is', $isLoggedIn, $username);
                              mysqli_stmt_execute($stmt);
                              echo "logged_in";
                            } else {
                                echo "Invalid password";
                            }
                        }
                    } else { 
                        echo "servername doesn't exist";
                    }
                } else {
                    echo "Oops! Something went wrong. Please try again later.";
                }

                // Close statement
                mysqli_stmt_close($stmt);
            }
        }else{
            echo 'Username or Password is empty.';
        }

        // Close connection
        mysqli_close($link);
    }
    else{
        //Logout
        $isLoggedIn = 0;
        $query = "UPDATE users SET appLoggedIn=? WHERE username=?";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, 'is', $isLoggedIn, $username);
        mysqli_stmt_execute($stmt);
            
        echo "logged_out";
    }
}
