
<?php
// Reference https://technosmarter.com/php/forgot-password-and-password-reset-form-in-php
require_once("config.php");

if (isset($_POST['subforgot'])) {
    $login = $_REQUEST['login_var'];
    $query = "select * from  users where (username='$login' OR email = '$login')";
    $res = mysqli_query($link, $query);
    $result = mysqli_fetch_array($res);
    if ($result) {
        $findresult = mysqli_query($link, "SELECT * FROM users WHERE (username='$login' OR email = '$login')");
        if ($res = mysqli_fetch_array($findresult)) {
            $oldftemail = $res['email'];
        }
        $token = bin2hex(random_bytes(50));
        $inresult = mysqli_query($link, "insert into pass_reset values('','$oldftemail','$token')");
        if ($inresult) {
            $FromName = "Lock-Me";
            $FromEmail = "lockmetest@stim-me.de";
            $ReplyTo = "lockmetest@stim-me.de";
            $credits = "https://lockmetest.stim-me.de/";
            $headers  = "MIME-Version: 1.0\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1\n";
            $headers .= "From: " . $FromName . " <" . $FromEmail . ">\n";
            $headers .= "Reply-To: " . $ReplyTo . "\n";
            $headers .= "X-Sender: <" . $FromEmail . ">\n";
            $headers .= "X-Mailer: PHP\n";
            $headers .= "X-Priority: 1\n";
            $headers .= "Return-Path: <" . $FromEmail . ">\n";
            $subject = "You have received password reset email";
            $msg = "Your password reset link <br> http://localhost:8081/php/form/password-reset.php?token=" . $token . " <br> Reset your password with this link .Click or open in new tab<br><br> <br> <br> <center>" . $credits . "</center>";
            if (mail($oldftemail, $subject, $msg)) {
                header("location:forgot_password_form.php?sent=1");
                echo 'email sent';
                $hide = '1';
            } else {
              //  header("location:forgot_password_form.php?servererr=1");
                echo 'server error';
            }
        } else {
            //header("location:forgot_password_form.php?something_wrong=1");
            echo 'something went wrong';
        }
    } else {
       //header("location:forgot_password_form.php?err=" . $login);
       echo 'username or email not found';
    }
}
?>
