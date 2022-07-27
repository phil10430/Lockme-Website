<?php
include 'helper_functions.php';


if(isset($_GET['token']))
{
    $token= test_input($_GET['token']);
}


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
