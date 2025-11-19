<?php 

require "login.php"; 
require "header.php";


if (isset($_SESSION['flash_message'])) {
    echo "<div style='background:#e0ffe0; color:#006600; padding:10px; border-radius:8px; margin:10px 0;'>
            {$_SESSION['flash_message']}
          </div>";
    unset($_SESSION['flash_message']); // Nachricht nur einmal anzeigen
}
?>
    <div class="card">

        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) { ?>
            <div class="text-center">
                <div class="card-header">
                    <h4>Hello <?php echo htmlspecialchars($_SESSION["username"]); ?></h4>
                </div>
            </div>

            <div class="card-body">

            <?php
                require "show_status.php";
                require "box_control.php";
            ?>
            
            <script src="autorefresh_script.js?v=<?php echo time(); ?>"></script>
            <script>refreshData("<?php echo $_SESSION["username"]; ?>");</script>

            </div>

            <div class="card-footer">
                <div class="text-center">
                    <a href="logout.php" class="btn btn-danger btn-round">Sign Out</a>
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