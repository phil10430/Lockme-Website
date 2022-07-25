<?php require "header.php"; ?>



<section class="container" id="main">
  <div class="card">


    <div class="card-header">

    </div>


    <div class="card-body">


      <form action="forgot_process.php" method="POST">
        
          <div class="form-group">

            <?php if (isset($_GET['err'])) {
              $err = $_GET['err'];
              echo '<p class="errmsg">No user found. </p>';
            }
            // If server error 
            if (isset($_GET['servererr'])) {
              echo "<p class='errmsg'>The server failed to send the message. Please try again later.</p>";
            }
            //if other issues 
            if (isset($_GET['somethingwrong'])) {
              echo '<p class="errmsg">Something went wrong.  </p>';
            }
            // If Success | Link sent 
            if (isset($_GET['sent'])) {
              echo "<div class='successmsg'>A reset link has been sent to your registered eMail. Please check your eMail. </div>";
            }
            ?>


            <?php if (!isset($_GET['sent'])) { ?>
              <label class="label_txt">Username or Email </label>
              <input type="text" name="login_var" value="<?php if (!empty($err)) {
                                                            echo  $err;
                                                          } ?>" class="form-control" required="">

          </div>
          <button type="submit" name="subforgot" class="btn btn-primary btn-group-lg form_btn">Send Link </button>
        <?php } ?>
      
      </form>

    </div>

    <div class="card-footer">
      <p>Have an account? <a href="index.php">Login</a> </p>
      <p>Don't have an account? <a href="register_page.php">Sign up</a> </p>
    </div>

  </div>
</section>

</body>

</html>