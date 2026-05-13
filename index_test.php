<?php
session_start();
require "login.php";
require_once __DIR__ . '/templates/header.php';
?>

<?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) { ?>

<div class="card">

    <div class="card-header">

        <?php
        if (isset($_SESSION['flash_message'])) {
            echo "<div class='alert'>{$_SESSION['flash_message']}</div>";
            unset($_SESSION['flash_message']);
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
        <a href="logout.php" class="btn-modern">SIGN OUT</a>
    </div>

</div>

<?php } else { ?>

<div class="landing-wrapper">


    <!-- HERO -->
    <section class="hero">

        <div class="hero-content">

            <h1>LockMeBox</h1>

            <p>
                The solution for modern key holding. <br> 
                Developed, designed and hand made in Germany exactly to your needs. <br> 
                No matter if you are locked up in your chastity belt or if you are wearing a lockable collar. <br> 
                In each case you or your key-holder need to store your keys.<br> 
                You can then choose whether to lock the box with a timer or a password.
            </p>

            <div class="hero-buttons">

                <a href="https://kinkystuffmade.com/product/lockmebox" target="_blank">
                    <span class="btn-modern">Shop Now</span>
                </a>

                <a href="https://play.google.com/store/apps/details?id=com.kinkystuffmade.lockmebox&hl=de" target="_blank">
                    <span class="btn-modern">Get App</span>
                </a>


            </div>

        </div>

    </section>

</div>

<?php } ?>


<?php require_once __DIR__ . '/templates/footer.php'; ?>