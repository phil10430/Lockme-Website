<?php 
require_once "config.php";
require "forgot_password.php"; 
require "header.php"; 
?>

<section class="container" id="main">
  <div class="card">
    <div class="card-header">
      Type in your Username or Email to reset your password.
    </div>

    <div class="card-body">

    <?php require "forgot_password_form.php"; ?>

    </div>

    <div class="card-footer">
      <p>Have an account? <a href="index.php">Login</a> </p>
      <p>Don't have an account? <a href="register_page.php">Sign up</a> </p>
    </div>

  </div>
</section>

</body>

</html>