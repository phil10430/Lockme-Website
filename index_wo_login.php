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


    <?php } else { ?>

        <div class="card-header">
            
            <?php 
            // Flash-Message für z. B. Registrierungserfolg
            if (isset($_SESSION['flash_message'])) {
                echo "<div class='alert alert-success mt-2'>{$_SESSION['flash_message']}</div>";
                unset($_SESSION['flash_message']);
            }
            ?>
        </div>
        
        <div class="card-body">
            <?php  include __DIR__ . '/templates/start_screen.php'; ?>
        </div>

       

    <?php } ?>

</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>

