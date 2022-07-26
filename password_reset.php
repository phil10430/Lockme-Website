<?php require_once("config.php"); ?>
<?php require "header.php"; ?>

<?php
if (isset($_GET['token'])) {
    $token = $_GET['token'];
}

//form for submit 
if (isset($_POST['sub_set'])) {

    extract($_POST);
    
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
    if ($res = mysqli_fetch_array($fetchresultok)) {
        $email = $res['email'];
    }
    if (isset($email) != '') {
        $emailtok = $email;
    } else {
        $error[] = 'Link has been expired or something missing ';
        $hide = 1;
    }
    if (!isset($error)) { 
 
        $password = password_hash($password, PASSWORD_DEFAULT);
        $resultresetpass = mysqli_query($link, "UPDATE users SET password='$password' WHERE email='$emailtok'");
        if ($resultresetpass) {
            $success = "<div class='successmsg'> Your password has been updated successfully. Login <a href='index.php' style='color:#fff;'> here. </a> </div>";
            $resultdel = mysqli_query($link, "DELETE FROM pass_reset WHERE token='$token'");
            $hide = 1;
        }
    }
}
?>

<div class="card">

    <div class="card-header">
    </div>
    <div class="card-body">
        <form action="" method="POST">
            <div class="form-group">
                <?php
                if (isset($error)) {
                    foreach ($error as $error) {
                        echo '<div class="errmsg">' . $error . '</div><br>';
                    }
                }
                if (isset($success)) {
                    echo $success;
                }
                ?>
                <?php if (!isset($hide)) { ?>
                    <label class="label_txt">Password </label>
                    <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="label_txt">Confirm Password </label>
                <input type="password" name="passwordConfirm" class="form-control" required>
            </div>
            <button type="submit" name="sub_set" class="btn btn-primary btn-group-lg form_btn">Reset Password</button>
        <?php } ?>
        </form>
    </div>
    <div class="card-footer">
    </div>

    </body>

    </html>