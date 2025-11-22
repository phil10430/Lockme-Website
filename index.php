<?php
session_start();
require "login.php"; 
require_once __DIR__ . '/templates/header.php'; 
?>

<div class="card">

    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) { ?>

        <div class="card-header">
            <?php 
            // Flash-Message im Header anzeigen
            if (isset($_SESSION['flash_message'])) {
                echo "<div class='alert alert-success mb-2'>{$_SESSION['flash_message']}</div>";
                unset($_SESSION['flash_message']); // nur einmal anzeigen
            }
            ?>
            Hello <?php echo htmlspecialchars($_SESSION["username"]); ?>
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

        <div class="card-footer">
            <a href="logout.php" class="btn btn-warning btn-round">SIGN OUT</a>
        </div>

    <?php } else { ?>

        <div class="card-header">
            Login to control your LOCKMEBOX!
            <?php 
            // Flash-Message für z. B. Registrierungserfolg
            if (isset($_SESSION['flash_message'])) {
                echo "<div class='alert alert-success mt-2'>{$_SESSION['flash_message']}</div>";
                unset($_SESSION['flash_message']);
            }
            ?>
        </div>

        <div class="card-body">
            <?php  include __DIR__ . '/templates/login_form.php'; ?>
        </div>

        <div class="card-footer">
            Don't have an account? <a href="register_page.php">Sign up now</a>.
        </div>

    <?php } ?>

</div>
