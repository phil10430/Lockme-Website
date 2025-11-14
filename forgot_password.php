<?php
include 'helper_functions.php';
// Reference https://technosmarter.com/php/forgot-password-and-password-reset-form-in-php

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $login = test_input($_POST["login_var"]);

    if (isValidEmail($login))
    {
    
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':email' => $login]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $email = $row['email'];
        } else {
            $email = null; // oder Fehlerbehandlung, falls nichts gefunden wurde
        }


        if (!empty($email)) {
            
            // if old entry with that email exists - delete old password reset entry
            $sql =  "DELETE FROM pass_reset WHERE email=?";
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt,"s", $email,);      
            }
            mysqli_stmt_execute($stmt);


            
            $token = bin2hex(random_bytes(50));

            $sql = "INSERT INTO pass_reset (email,token) VALUES (?,?)";

            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt,"ss", $email, $token,);      
            }

            if (mysqli_stmt_execute($stmt)) {
                $FromName = "Lock-Me";
                $FromEmail = "lockmetest@stim-me.de";
                $ReplyTo = "lockmetest@stim-me.de";
                $credits = "https://lockmebox.de/";
                $headers  = "MIME-Version: 1.0\n";
                $headers .= "Content-type: text/html; charset=iso-8859-1\n";
                $headers .= "From: " . $FromName . " <" . $FromEmail . ">\n";
                $headers .= "Reply-To: " . $ReplyTo . "\n";
                $headers .= "X-Sender: <" . $FromEmail . ">\n";
                $headers .= "X-Mailer: PHP\n";
                $headers .= "X-Priority: 1\n";
                $headers .= "Return-Path: <" . $FromEmail . ">\n";
                $subject = "You have received a password reset email";


                $mlink = "https://lockmebox.de/password_reset_page.php?token=".$token;
                $msg = "
                    <html>
                    <head>
                        <title>Your password reset link</title>
                    </head>
                    <body>
                        Reset your password with this <a href=" . $mlink . "> Link</a>. 
                    </body>
                    </html>";
                
                if (mail($email, $subject, $msg, $headers,'-f'.$FromEmail)) {
                    header("location:forgot_password_page.php?sent=1");
                    echo 'email sent';
                    $hide = '1';
                } else {
                    header("location:forgot_password_page.php?servererr=1");
                }
                
            } else {
                header("location:forgot_password_page.php?something_wrong=1"); 
            }
        } else {
            header("location:forgot_password_page.php?err=".$login); 
        }
    }else {
        header("location:forgot_password_page.php?emailformat_invalid=1"); 
    }


}
?>
