<?php if (!empty($login_err)): ?>
    <div class="auth-message auth-error">
        <span class="auth-message-icon">⚠</span>
        <span><?= htmlspecialchars($login_err) ?></span>
    </div>
<?php endif; ?>

<div class="overlay-card">
    <img class="bg-image" src="/assets/images/lmb_start.png" alt="Background">
    <div class="card-content">
        <div class="login-card">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                <div class="form-group" style="margin-bottom:20px;">
                    <input
                        type="text"
                        name="username"
                        class="form-control clean-input <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>"
                        value="<?php echo $username; ?>"
                        placeholder="Username"
                        required
                    >
                    <span class="invalid-feedback" style="font-size:12px;"><?php echo $username_err; ?></span>
                </div>

                <div class="form-group" style="margin-bottom:4px; position:relative;">
                    <input
                        type="password"
                        name="password"
                        id="login-password"
                        class="form-control clean-input <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                        placeholder="Password"
                        style="padding-right:28px;"
                        required
                    >
                    <span onclick="togglePassword('login-password', this)"
                        style="position:absolute; right:4px; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--primary-color); opacity:0.5;">
                        <i class="ti ti-eye" style="font-size:16px;"></i>
                    </span>
                    <span class="invalid-feedback" style="font-size:12px;"><?php echo $password_err; ?></span>
                </div>

                <div style="text-align:right; margin-bottom:24px;">
                    <a href="forgot_password_page.php" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-round w-100" name="submitLogin">LOGIN</button>

            </form>
        </div>
    </div>
</div>