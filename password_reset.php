<?php
require_once __DIR__ . '/includes/helper_functions.php';

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


    $sql = "SELECT email FROM pass_reset WHERE token = :token";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $email = $row['email'];
    
    
  
    if (isset($email) != '') {
        $emailtok = $email;
    } else {
        $error[] = 'Link has been expired.';
        $hide = 1;
    }

    if (!isset($error)) {
        $password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE users SET password = :password WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':email', $emailtok, PDO::PARAM_STR);
        $updateSuccess = $stmt->execute();


       if ($updateSuccess) {
            $success = "<div class='alert-warning'> Your password has been updated successfully. Login <a href='index.php'>here</a>. </div>";

            $sql = "DELETE FROM pass_reset WHERE token = :token";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $resultdel = $stmt->execute();


            $hide = 1;
       }

    }

}
