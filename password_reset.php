<?php
include 'helper_functions.php';


if(isset($_GET['token']))
{
    $token= test_input($_GET['token']);
}


if (isset($_POST['sub_set'])) {

    $password = trim($_POST["password"]);
    $passwordConfirm =  trim($_POST["passwordConfirm"]);

    if ($password == '') {
        $error[] = 'Please enter a password.';
    }
    if ($passwordConfirm == '') {
        $error[] = 'Please confirm password.';
    }
    if ($password != $passwordConfirm) {
        $error[] = 'Password did not match.';
    }
    if (strlen($password) < 6) { // min 
        $error[] = 'Password must have atleast 6 characters.';
    }


    $stmt = $link -> prepare("SELECT email FROM pass_reset WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $email = $row['email'];
    
    
  
    if (isset($email) != '') {
        $emailtok = $email;
    } else {
        $error[] = 'Link has been expired or something missing: ' . $token;
        $hide = 1;
    }

    if (!isset($error)) {
        $password = mysqli_real_escape_string($link,$password);
        $password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $link -> prepare( "UPDATE users SET password=? WHERE email=?");
        $stmt->bind_param("ss", $password, $emailtok);
        $updateSuccess = $stmt->execute();
       

       if ($updateSuccess) {
            $success = "<div class='alert-warning'> Your password has been updated successfully. Login <a href='index.php'>here</a>. </div>";

            $resultdel = mysqli_query($link, "DELETE FROM pass_reset WHERE token='$token'");

            $hide = 1;
       }

    }

}
