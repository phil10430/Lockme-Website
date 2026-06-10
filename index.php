<?php
session_start();
require "login.php";

/* 🔥 WICHTIG: dynamisch setzen */
$bodyClass = (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
    ? ""              // logged in → normale App
    : "landing-page"; // guest → landing page

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

</div>

<?php } else { ?>

<div class="landing-wrapper">



    <section class="hero">

    <div class="hero-content">

        <h1>LockMeBox</h1>

        <p class="hero-subtitle">
            Key holding made easy.
        </p>

        <p class="hero-text">
            Password or timer-based locking for modern play.<br>
            Designed by kinksters, for kinksters.
        </p>

        <div class="hero-buttons">

            <a href="https://kinkystuffmade.com/product/lockmebox" target="_blank">
                    <span class="btn-modern">Shop Now</span>
            </a>
            <a href="https://lockmebox.com/control_center.php">
                    <span class="btn-modern">Sign In</span>
            </a>

        </div>
         <div class="hero-buttons">

           


        </div>

    </div>

    </section>

</div>

<?php } ?>


<?php require_once __DIR__ . '/templates/footer.php'; ?>