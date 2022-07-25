<?php require "password_reset_control.php"; ?>
<?php require "header.php"; ?>

<section class="container" id="main">
    <div class="card">
        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
            header("location:index.php");
        }
        ?>
        <div class="text-center">
            <div class="card-header">
                <h4>Reset Password</h4>
            </div>
        </div>
        <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <div class="form-group">  
                        <?php
                          if(isset($_GET['token']))
                          {
                          $token= $_GET['token'];
                          }
                          
                          ?>       
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

    </div>
</section>

</body>

</html>


