<?php require "reset_password.php"; ?>
<?php require "header.php"; ?>

<body>

    <section class="container" id="register">

        <div class="card">

            <div class="card-header">
                <h2>Reset Password</h2>
                <p>Please fill out this form to reset your password.</p>
            </div>

            <div class="card-body">

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>">
                        <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                    </div>
                    <div class="form-group">
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a class="btn btn-link ml-2" href="index.php">Cancel</a>
                    </div>

                </form>

            </div>



            <div class="card">

    </section>







</body>

</html>