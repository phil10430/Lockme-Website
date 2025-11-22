<?php
session_start();
require "login.php"; 
require "header.php"; // liefert <html>, <head> und <body>
?>

<div class="card mt-4">

    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) { ?>

        <div class="card-header text-center">
            <?php 
            // Flash-Message im Header anzeigen
            if (isset($_SESSION['flash_message'])) {
                echo "<div class='alert alert-success mb-2'>{$_SESSION['flash_message']}</div>";
                unset($_SESSION['flash_message']); // nur einmal anzeigen
            }
            ?>
            <h4>Hello <?php echo htmlspecialchars($_SESSION["username"]); ?></h4>
        </div>

        <div class="card-body">
            <?php
                require "show_status.php";
                require "box_control.php";
            ?>
            
            <script>
                refreshData("<?php echo $_SESSION["username"]; ?>");
            </script>
        </div>

        <div class="card-footer text-center">
            <a href="logout.php" class="btn btn-danger btn-round">Sign Out</a>
        </div>

    <?php } else { ?>

        <div class="card-header text-center">
            Login to control your x!
            <?php 
            // Flash-Message für z. B. Registrierungserfolg
            if (isset($_SESSION['flash_message'])) {
                echo "<div class='alert alert-success mt-2'>{$_SESSION['flash_message']}</div>";
                unset($_SESSION['flash_message']);
            }
            ?>
        </div>

        <div class="card-body">
            <?php require "login_form.php"; ?>
        </div>

        <div class="card-footer text-center">
            Don't have an account? <a href="register_page.php">Sign up now</a>.
        </div>

    <?php } ?>

</div>
