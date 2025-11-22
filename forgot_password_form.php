<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

    <div class="form-group">

      <?php
        session_start();

        if (isset($_GET['err'])) {
            $_SESSION['flash_message'] = "No user found.";
            $_SESSION['flash_type'] = "danger";
        } 
        elseif (isset($_GET['servererr'])) {
            $_SESSION['flash_message'] = "The server failed to send the message. Please try again later.";
            $_SESSION['flash_type'] = "danger";
        }
        elseif (isset($_GET['emailformat_invalid'])) {
            $_SESSION['flash_message'] = "Email format is not valid.";
            $_SESSION['flash_type'] = "danger";
        }
        elseif (isset($_GET['something_wrong'])) {
            $_SESSION['flash_message'] = "Something went wrong.";
            $_SESSION['flash_type'] = "danger";
        }
        elseif (isset($_GET['sent'])) {
            $_SESSION['flash_message'] = "A reset link has been sent to your registered email. Please check your email.";
            $_SESSION['flash_type'] = "success";
        }

        // Wenn eine Message gesetzt wurde → sofort zur index.php
        if (isset($_SESSION['flash_message'])) {
            header("Location: index.php");
            exit;
        }
        ?>



        <?php if (!isset($_GET['sent'])) { ?>
            <div class="overlay-card">

            <img class="bg-image" src="pictures/icon_box_unclear.png" alt="Background">

                <div class="card-content">

                    <div class="login-card">

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                           <div class="form-group">
                            <input 
                                type="email" 
                                name="login_var" 
                                value="<?php echo (!empty($error_forgot)) ? $error_forgot : ''; ?>" 
                                class="form-control clean-input <?php echo (!empty($error_forgot)) ? 'is-invalid' : ''; ?>" 
                                placeholder="Your Email" 
                                required
                            >
                            <span class="invalid-feedback"><?php echo $error_forgot; ?></span>
                        </div>

                        <div class="form-group">
                            <button 
                                type="submit" 
                                name="subforgot" 
                                class="btn btn-warning btn-round w-100"
                            >
                                Send Link
                            </button>
                        </div>

                        </form>


                    </div>

                </div>
            </div>
        <?php } ?>


</form>