<?php
if (!empty($login_err)) {
    
    if($login_err=="Invalid password."){
        echo '<div class="alert alert-danger">' . $login_err . '<a href="reset-password.php"> Reset Your Password</a></div>';
    } else{
        echo '<div class="alert alert-danger">' . $login_err . '</div>';
    }
}
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
        <span class="invalid-feedback"><?php echo $username_err; ?></span>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
        <span class="invalid-feedback"><?php echo $password_err; ?></span>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Login" name="submitLogin">
    </div>
    <p>Don't have an account? test <a href="register_page.php">Sign up now</a>.</p>
</form>