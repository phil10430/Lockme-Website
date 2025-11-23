<?php 
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/templates/header.php'; 
?>

<div class="card">
  <div class="card-header">

    <?php if (!empty($_SESSION['flash_message'])): ?>
      <div class="alert alert-success text-center">
        <?= $_SESSION['flash_message']; ?>
      </div>
      <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    Type in your Email to reset your password.
  </div>

  <div class="card-body">
      <?php include __DIR__ . '/templates/forgot_password_form.php'; ?>
  </div>

  <div class="card-footer text-center">
      <p>Have an account? <a href="index.php">Login</a></p>
      <p>Don't have an account? <a href="register_page.php">Sign up</a></p>
  </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
