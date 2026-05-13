<?php require_once __DIR__ . '/includes/config.php'; ?>
<?php require "register.php"; ?>
<?php require_once __DIR__ . '/templates/header.php'; ?>

<div class="card">
    <div class="card-header">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger" style="font-size:13px;">⚠️ <?php echo $errors['general']; ?></div>
        <?php endif; ?>
        Create your account
    </div>
    <div class="card-body">
        <?php include __DIR__ . '/templates/register_form.php'; ?>
    </div>
    <div class="card-footer">
        Already have an account? <a href="index.php">Login here</a>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>