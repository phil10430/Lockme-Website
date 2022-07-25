<?php
require_once "config.php";

    if(isset($_GET['token']))
    {
    $token= $_GET['token'];
    }

    
    echo 'token<'.$token.'>';
    //form for submit 
    if (isset($_POST['sub_set'])) {
        $password = test_input($_POST["password"]);
        $passwordConfirm =  test_input($_POST["passwordConfirm"]);

        if ($password == '') {
            $error[] = 'Please enter the password.';
        }
        if ($passwordConfirm == '') {
            $error[] = 'Please confirm the password.';
        }
        if ($password != $passwordConfirm) {
            $error[] = 'Passwords do not match.';
        }
        if (strlen($password) < 5) { // min 
            $error[] = 'The password is 6 characters long.';
        }
        if (strlen($password) > 50) { // Max 
            $error[] = 'Password: Max length 50 Characters Not allowed';
        }
         
        $fetchresultok = mysqli_query($link, "SELECT email FROM pass_reset WHERE token='$token'");
        if($res = mysqli_fetch_array($fetchresultok))
        {
            $email= $res['email']; 
        }
        if (isset($email) != '') {
            $emailtok = $email;
        } else {
            $error[] = 'Link has been expired or something missing ';
            $hide = 1;
        }
        if (!isset($error)) {
            $options = array("cost" => 4);
            $password = password_hash($password, PASSWORD_BCRYPT, $options);
            $resultresetpass = mysqli_query($link, "UPDATE users SET password='$password' WHERE email='$emailtok'");
            if ($resultresetpass) {
                $success = "<div class='successmsg'><span style='font-size:100px;'>&#9989;</span> <br> Your password has been updated successfully.. <br> <a href='index.php' style='color:#fff;'>Login here... </a> </div>";
                $resultdel = mysqli_query($link, "DELETE FROM pass_reset WHERE token='$token'");
                $hide = 1;
            }
        }
    }


function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
