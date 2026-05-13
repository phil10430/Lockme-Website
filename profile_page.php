<?php require_once __DIR__ . '/includes/config.php'; ?>
<?php require "profile.php"; ?>
<?php require_once __DIR__ . '/templates/header.php'; ?>

<div class="card">
    <div class="card-header">
        Account — <?php echo htmlspecialchars($username); ?>
    </div>
    <div class="card-body">

        <?php // Info ?>
        <p style="font-size:12px; color:var(--primary-color); opacity:0.5; margin-bottom:24px;">
            Member since <?php echo date("d.m.Y", strtotime($user['created_at'])); ?>
        </p>

        <?php // Passwort ändern ?>
        <p style="font-size:13px; color:var(--primary-color); margin-bottom:12px; letter-spacing:1px;">CHANGE PASSWORD</p>

        <?php if (!empty($errors['password'])): ?>
            <div class="alert alert-danger" style="font-size:13px;">⚠️ <?php echo $errors['password']; ?></div>
        <?php endif; ?>
        <?php if (!empty($success['password'])): ?>
            <div class="alert alert-success" style="font-size:13px;">✓ <?php echo $success['password']; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group" style="margin-bottom:20px; position:relative;">
                <input type="password" name="current_password" id="current-pw"
                    class="form-control clean-input" placeholder="Current Password"
                    style="padding-right:28px;" required>
                <span onclick="togglePassword('current-pw', this)"
                    style="position:absolute; right:4px; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--primary-color); opacity:0.5;">
                    <i class="ti ti-eye" style="font-size:16px;"></i>
                </span>
            </div>
            <div class="form-group" style="margin-bottom:20px; position:relative;">
                <input type="password" name="new_password" id="new-pw"
                    class="form-control clean-input" placeholder="New Password"
                    style="padding-right:28px;" required>
                <span onclick="togglePassword('new-pw', this)"
                    style="position:absolute; right:4px; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--primary-color); opacity:0.5;">
                    <i class="ti ti-eye" style="font-size:16px;"></i>
                </span>
            </div>
            <div class="form-group" style="margin-bottom:20px; position:relative;">
                <input type="password" name="confirm_new_password" id="confirm-new-pw"
                    class="form-control clean-input" placeholder="Confirm New Password"
                    style="padding-right:28px;" required>
                <span onclick="togglePassword('confirm-new-pw', this)"
                    style="position:absolute; right:4px; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--primary-color); opacity:0.5;">
                    <i class="ti ti-eye" style="font-size:16px;"></i>
                </span>
            </div>
            <div class="form-group" style="margin-bottom:32px;">
                <button type="submit" class="btn btn-round" name="changePassword">Update Password</button>
            </div>
        </form>

        <hr style="border-color:rgba(255,255,255,0.1); margin-bottom:24px;">

        <?php // E-Mail ändern ?>
        <p style="font-size:13px; color:var(--primary-color); margin-bottom:12px; letter-spacing:1px;">CHANGE E-MAIL</p>
        <p style="font-size:12px; color:var(--primary-color); opacity:0.5; margin-bottom:12px;">
            Current: <?php echo htmlspecialchars($user['email']); ?>
        </p>

        <?php if (!empty($errors['email'])): ?>
            <div class="alert alert-danger" style="font-size:13px;">⚠️ <?php echo $errors['email']; ?></div>
        <?php endif; ?>
        <?php if (!empty($success['email'])): ?>
            <div class="alert alert-success" style="font-size:13px;">✓ <?php echo $success['email']; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group" style="margin-bottom:20px;">
                <input type="email" name="new_email"
                    class="form-control clean-input" placeholder="New E-Mail" required>
            </div>
            <div class="form-group" style="margin-bottom:20px; position:relative;">
                <input type="password" name="email_password" id="email-pw"
                    class="form-control clean-input" placeholder="Confirm with Password"
                    style="padding-right:28px;" required>
                <span onclick="togglePassword('email-pw', this)"
                    style="position:absolute; right:4px; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--primary-color); opacity:0.5;">
                    <i class="ti ti-eye" style="font-size:16px;"></i>
                </span>
            </div>
            <div class="form-group" style="margin-bottom:32px;">
                <button type="submit" class="btn btn-round" name="changeEmail">Update E-Mail</button>
            </div>
        </form>

        <hr style="border-color:rgba(255,255,255,0.1); margin-bottom:24px;">

        <?php // Account löschen ?>
        <p style="font-size:13px; color:#FF6B6B; margin-bottom:12px; letter-spacing:1px;">DELETE ACCOUNT</p>

        <?php if (!empty($errors['delete'])): ?>
            <div class="alert alert-danger" style="font-size:13px;">⚠️ <?php echo $errors['delete']; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group" style="margin-bottom:20px; position:relative;">
                <input type="password" name="delete_password" id="delete-pw"
                    class="form-control clean-input" placeholder="Confirm with Password"
                    style="padding-right:28px;" required>
                <span onclick="togglePassword('delete-pw', this)"
                    style="position:absolute; right:4px; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--primary-color); opacity:0.5;">
                    <i class="ti ti-eye" style="font-size:16px;"></i>
                </span>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-round" name="deleteAccount"
                    onclick="return confirm('Are you sure? This cannot be undone.');"
                    style="border-color:#FF6B6B; color:#FF6B6B;">
                    Delete Account
                </button>
            </div>
        </form>

    </div>
    <div class="card-footer">
        <a href="index.php" class="btn btn-round">Back</a>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>