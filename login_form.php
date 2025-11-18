<?php
if (!empty($login_err)) {
    echo '<div class="alert alert-danger">';
    echo $login_err;
    if ($login_err == "Invalid password.") {
        echo '<br><a href="forgot_password_page.php" class="forgot-link">Passwort vergessen?</a>';
    }
    echo '</div>';
}
?>
<div class="login-card">

    <img class="bg-image" src="pictures/icon_box_closed.png" alt="Background">

    <div class="card-content">


        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input 
                    type="text" 
                    name="username" 
                    class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" 
                    value="<?php echo $username; ?>"
                >
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input 
                    type="password" 
                    name="password" 
                    class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                >
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>

            <button type="submit" class="btn-modern" name="submitLogin">Login</button>
        </form>

    </div>
</div>
