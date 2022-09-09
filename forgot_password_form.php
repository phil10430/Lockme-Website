<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

    <div class="form-group">


        <?php if (isset($_GET['err'])) {
            $err = $_GET['err'];
            echo '<p class="errmsg">No user found. </p>';
        }
        // If server error 
        if (isset($_GET['servererr'])) {
            echo "<p class='errmsg'>The server failed to send the message. Please try again later.</p>";
        }
         // If server error 
         if (isset($_GET['emailformat_invalid'])) {
            echo "<p class='errmsg'>Email format is not valid.</p>";
        }
        //if other issues 
        if (isset($_GET['something_wrong'])) {
            echo '<p class="errmsg">Something went wrong.  </p>';
        }
        // If Success | Link sent 
        if (isset($_GET['sent'])) {
            echo "<div class='successmsg'>A reset link has been sent to your registered email. Please check your email. </div>";
        }
        ?>


        
        <?php if (!isset($_GET['sent'])) { ?>
            <label class="label_txt">Your Email: </label>
            <input type="text" name="login_var" value="<?php if (!empty($error_forgot)) {
                                                            echo $error_forgot;
                                                        } ?>" class="form-control" required="">
            <span class="invalid-feedback"><?php echo $error_forgot; ?></span>
            </div>

            <div class="form-group">
                <button type="submit" name="subforgot" class="btn btn-primary btn-group-lg form_btn">Send Link </button>
            </div>
        <?php } ?>


</form>