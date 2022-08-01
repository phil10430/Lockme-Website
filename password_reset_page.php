<?php
require_once "config.php";
require "header.php";
require "password_reset.php"; 
?>

<div class="card">

    <div class="card-header">
        Reset your password:
    </div>

    <div class="card-body">

        <!-- form cannot be included from external file -->
        <!-- reference: https://technosmarter.com/php/forgot-password-and-password-reset-form-in-php -->
        <form action="" method="POST">

            <div class="form-group">
                <?php
                if (isset($error)) {
                    foreach ($error as $error) {
                        echo '<div class="alert-warning">' . $error . '</div><br>';
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

            <div class="form-group">
                <button type="submit" name="sub_set" class="btn btn-primary btn-group-lg form_btn">Reset Password</button>
            </div>
        <?php } ?>

        </form>

        <!-- form cannot be included from external file -->
        

    </div>

    <div class="card-footer">
     <p>Have an account? <a href="index.php">Login</a> </p>
      <p>Don't have an account? <a href="register_page.php">Sign up</a> </p>
    </div>

</div>

</body>

</html>