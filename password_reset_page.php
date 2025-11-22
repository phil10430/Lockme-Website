<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/templates/header.php'; 
require "password_reset.php"; 
?>

<div class="card">

    <div class="card-header">
        Reset your password:
    </div>
        <div class="overlay-card">

            <img class="bg-image" src="/assets/images/icon_box_unclear.png" alt="Background">

            <div class="card-content">

                <div class="login-card">

                    <form action="" method="POST">

                        <!-- Error / Success Messages -->
                        <?php if (isset($error)) { ?>
                            <?php foreach ($error as $msg) { ?>
                                <div class="alert alert-danger mb-2"><?php echo $msg; ?></div>
                            <?php } ?>
                        <?php } ?>

                        <?php if (isset($success)) { ?>
                            <div class="alert alert-success mb-3"><?php echo $success; ?></div>
                        <?php } ?>

                        <?php if (!isset($hide)) { ?>

                            <!-- New Password -->
                            <div class="form-group">
                                <input 
                                    type="password" 
                                    name="password" 
                                    class="form-control clean-input <?php echo (!empty($error)) ? 'is-invalid' : ''; ?>"
                                    placeholder="New Password"
                                    required
                                >
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-group">
                                <input 
                                    type="password" 
                                    name="passwordConfirm" 
                                    class="form-control clean-input <?php echo (!empty($error)) ? 'is-invalid' : ''; ?>"
                                    placeholder="Confirm Password"
                                    required
                                >
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group">
                                <button 
                                    type="submit" 
                                    name="sub_set" 
                                    class="btn btn-warning btn-round w-100"
                                >
                                    Reset Password
                                </button>
                            </div>

                        <?php } ?>

                    </form>

                </div>

            </div>

        </div>



    <div class="card-footer">
     <p>Have an account? <a href="index.php">Login</a> </p>
      <p>Don't have an account? <a href="register_page.php">Sign up</a> </p>
    </div>

</div>

</body>

</html>