<?php require "header.php"; ?>
<body>
<div class="container">
	<div class="row">
		<div class="col-sm-4">
		</div>
		<div class="col-sm-4">
 	<form action="forgot_process.php" method="POST">
    <div class="login_form">
  <div class="form-group">
 <?php if(isset($_GET['err'])){
 $err=$_GET['err'];
 echo '<p class="errmsg">No user found. </p>';
} 
// If server error 
if(isset($_GET['servererr'])){ 
echo "<p class='errmsg'>The server failed to send the message. Please try again later.</p>";
   }
   //if other issues 
   if(isset($_GET['somethingwrong'])){ 
 echo '<p class="errmsg">Something went wrong.  </p>';
   }
// If Success | Link sent 
if(isset($_GET['sent'])){
echo "<div class='successmsg'>Reset link has been sent to your registered email id . Kindly check your email. It can be taken 2 to 3 minutes to deliver on your email id . </div>"; 
  }
  ?>
  <?php if(!isset($_GET['sent'])){ ?>
    <label class="label_txt">Username or Email </label>
    <input type="text" name="login_var" value="<?php if(!empty($err)){ echo  $err; } ?>" class="form-control" required="">
  </div>
  <button type="submit" name="subforgot" class="btn btn-primary btn-group-lg form_btn">Send Link </button>
  <?php } ?>
</div>
</form>
   <br> 
   <p>Have an account? <a href="login.php">Login</a> </p>
    <p>Don't have an account? <a href="signup.php">Sign up</a> </p> 
		</div>
		<div class="col-sm-4">
		</div>
	</div>
</div> 
</body>
</html>
