<?php require "login.php"; ?>
<?php require "header.php"; ?>


<section class="container" id="main">
    <nav class="navbar navbar-light bg-light">
        <a class="navbar-brand" href="#">
            <img src="pictures/logo.png" width="30" height="30" class="d-inline-block align-center" alt="">
            LockMe
        </a>
    </nav>
    <div class="card">

        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) { ?>
            <div class="text-center">
                <div class="card-header">
                    <h4>Hello <?php echo htmlspecialchars($_SESSION["username"]); ?></h4>
                </div>
            </div>

            <div class="card-body">

            <?php
           
            // clear wished action from database on refresh
            require_once "config.php";
            $query = "UPDATE users SET WishedAction='' WHERE username=?";
            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, 's', $_SESSION["username"]);
            mysqli_stmt_execute($stmt);
            
            require "show_status.php";
            require "box_control.php";
       
            ?>

            <script>refreshData("<?php echo $_SESSION["username"]; ?>");</script>

            </div>

            <div class="card-footer">
                <div class="text-center">
                    <a href="logout.php" class="btn btn-danger">Sign Out</a>
                </div>
            </div>

        <?php } else { ?>
            <div class="card-header">
                Login to use LockMe-Box.
            </div>
            <div class="card-body">
                <?php require "login_form.php"; ?>
            </div>
            <div class="card-footer">
                Don't have an account? <a href="register_page.php">Sign up now</a>.
            </div>
        <?php } ?>

    </div>
</section>

</body>

</html>