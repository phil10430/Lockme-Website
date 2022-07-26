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
        $sql = "INSERT INTO pass_reset (email,token) VALUES (?,?)";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt,"ss", $oldftemail, $token,);      
        }
        if (mysqli_stmt_execute($stmt)) {
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
            $subject = "You have received a password reset email";


            $mlink = "https://lockmetest.stim-me.de/password_reset.php?token=".$token;
            $msg = "
                <html>
                <head>
                    <title>Your password reset link</title>
                </head>
                <body>
                    Reset your password with this <a href=" . $mlink . "> Link</a>. 
                </body>
                </html>";

            if (@mail($oldftemail, $subject, $msg, $headers,'-f'.$FromEmail)) {
                header("location:forgot_password_form.php?sent=1");
                echo 'email sent';
                $hide = '1';
            } else {
                header("location:forgot_password_form.php?servererr=1");
                // uncomment following line to see error message
                // echo mysqli_error($link);
            }
        } else {
            header("location:forgot_password_form.php?something_wrong=1");
            // uncomment following line to see error message
            // echo mysqli_error($link);
        }
    } else {
       header("location:forgot_password_form.php?err=" . $login);
        // uncomment following line to see error message
       // echo mysqli_error($link); 
    }
}
?>
