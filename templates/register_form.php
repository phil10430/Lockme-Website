<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

    <div class="form-group" style="margin-bottom:20px;">
        <input type="text" name="username"
            class="form-control clean-input <?php echo (!empty($errors['username'])) ? 'is-invalid' : ''; ?>"
            value="<?php echo $username; ?>" placeholder="Username" required>
        <span class="invalid-feedback" style="font-size:12px;"><?php echo $errors['username']; ?></span>
    </div>

    <div class="form-group" style="margin-bottom:20px;">
        <input type="email" name="email"
            class="form-control clean-input <?php echo (!empty($errors['email'])) ? 'is-invalid' : ''; ?>"
            value="<?php echo $email; ?>" placeholder="E-Mail" required>
        <span class="invalid-feedback" style="font-size:12px;"><?php echo $errors['email']; ?></span>
    </div>

    <div class="form-group" style="margin-bottom:4px; position:relative;">
        <input type="password" name="password" id="register-password"
            class="form-control clean-input <?php echo (!empty($errors['password'])) ? 'is-invalid' : ''; ?>"
            placeholder="Password" style="padding-right:28px;" required>
        <span onclick="togglePassword('register-password', this)"
            style="position:absolute; right:4px; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--primary-color); opacity:0.5;">
            <i class="ti ti-eye" style="font-size:16px;"></i>
        </span>
        <span class="invalid-feedback" style="font-size:12px;"><?php echo $errors['password']; ?></span>
    </div>

    <div class="form-group" style="margin-bottom:20px; position:relative;">
        <input type="password" name="confirm_password" id="register-confirm"
            class="form-control clean-input <?php echo (!empty($errors['confirm_password'])) ? 'is-invalid' : ''; ?>"
            placeholder="Confirm Password" style="padding-right:28px;" required>
        <span onclick="togglePassword('register-confirm', this)"
            style="position:absolute; right:4px; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--primary-color); opacity:0.5;">
            <i class="ti ti-eye" style="font-size:16px;"></i>
        </span>
        <span class="invalid-feedback" style="font-size:12px;"><?php echo $errors['confirm_password']; ?></span>
    </div>

    <div class="form-group" style="margin-bottom:20px;">
        <label style="color:var(--primary-color); font-size:13px; display:flex; gap:8px; align-items:flex-start;">
            <input type="checkbox" name="accept_terms" id="accept-terms" required style="margin-top:3px; flex-shrink:0;">
            <span>I agree to the 
                <a href="/legal/terms.php" target="_blank" style="color:var(--primary-color); opacity:0.7;">Terms of Service</a> and 
                <a href="/legal/privacy.php" target="_blank" style="color:var(--primary-color); opacity:0.7;">Privacy Policy</a>.
            </span>
        </label>
        <?php if (!empty($errors['terms'])): ?>
            <span style="color:#FF6B6B; font-size:12px;"><?php echo $errors['terms']; ?></span>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-modern w-100" name="submitRegister">Register</button>
    </div>

</form>