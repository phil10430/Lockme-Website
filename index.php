
<?php
session_start();
require "login.php";

/* Set the body class dynamically */
$bodyClass = (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
    ? ""
    : "landing-page";

/* Count currently locked lockees */
$lockedCount = 0;

if ($bodyClass === "landing-page") {
    try {
        $sql = "
            SELECT COUNT(*)
            FROM box_data_actual
            WHERE lock_status = 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $lockedCount = (int) $stmt->fetchColumn();

    } catch (PDOException $e) {
        $lockedCount = 0;
    }
}

require_once __DIR__ . '/templates/header.php';
?>

<?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) { ?>

<div class="card">

    <div class="card-header">

        <?php
        if (isset($_SESSION['flash_message'])) {
            echo "<div class='alert'>" .
                 htmlspecialchars($_SESSION['flash_message']) .
                 "</div>";

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
            refreshData("<?php echo htmlspecialchars($_SESSION["username"]); ?>");
        </script>

    </div>

</div>

<?php } else { ?>

<div class="landing-wrapper">

    <section class="hero">

        <div class="hero-content">

            <div class="hero-brand">
                LockMeBox
            </div>

            <h1>Key holding made easy.</h1>

            <p class="hero-text">
                Password or timer-based locking for modern play.
                Designed by kinksters, for kinksters.
            </p>

            <?php if ($lockedCount > 0): ?>

                <div class="hero-community-status">

                    <a href="lockees.php">
                        <span class="hero-community-count">
                            <?php echo $lockedCount; ?>
                        </span>
                        users are currently locked.
                    </a>

                    <span class="hero-community-next">
                        Be the next one.
                    </span>

                </div>

            <?php endif; ?>




            <div class="hero-buttons">

                <a
                    href="https://kinkystuffmade.com/product/lockmebox"
                    target="_blank"
                    rel="noopener"
                >
                    <span class="btn-modern">Shop Now</span>
                </a>

                <a href="https://lockmebox.com/control_center.php">
                    <span class="btn-modern">Sign In</span>
                </a>

            </div>

        </div>

    </section>

</div>

<?php } ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
