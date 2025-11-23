<?php
if (!empty($login_err)) {
    echo '<div class="alert alert-danger">';
    echo "❌ " . $login_err;
    if ($login_err == "Invalid password.") {
        echo '<br><a href="forgot_password_page.php" class="forgot-link">Forgot your password?</a>';
    }
    echo '</div>';
}
?>
<div class="overlay-card">

    <img class="bg-image" src="/assets/images/icon_box_unclear.png" alt="Background">

    <div class="card-content">

        <div class="login-card">

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <input 
                        type="text" 
                        name="username" 
                        class="form-control clean-input <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                        value="<?php echo $username; ?>"
                        placeholder="Username"
                        required
                    >
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>

                <div class="form-group">
                    <input 
                        type="password" 
                        name="password" 
                        class="form-control clean-input <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                        placeholder="Password"
                        required
                    >
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>

                <button type="submit"  class="btn btn-round w-100" name="submitLogin">Login</button>
            </form>
        </div>

    </div>
</div>
