<?php
session_start();
require "login.php";

/* Set the body class dynamically */
$bodyClass = (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
    ? ""
    : "landing-page";

/* Landing page data: locked count + community preview */
$lockedCount    = 0;
$publicCount    = 0;
$previewMembers = [];

if ($bodyClass === "landing-page") {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM box_data_actual
            WHERE lock_status = 1
        ");
        $stmt->execute();
        $lockedCount = (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        $lockedCount = 0;
    }

    try {
        $publicCount = (int) $pdo->query(
            "SELECT COUNT(*) FROM user_details WHERE profile_visibility = 'public'"
        )->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT u.id, u.username, ud.avatar_path, ud.headline,
                   EXISTS (
                       SELECT 1
                       FROM user_boxes ub
                       JOIN box_data_actual bda ON bda.box_id = ub.box_id
                       WHERE ub.user_id = u.id AND bda.lock_status = 1
                   ) AS is_locked
            FROM users u
            JOIN user_details ud ON ud.user_id = u.id
            WHERE ud.profile_visibility = 'public'
              AND ud.avatar_path IS NOT NULL AND ud.avatar_path <> ''
            ORDER BY RAND()
            LIMIT 6
        ");
        $stmt->execute();
        $previewMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $previewMembers = [];
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
        document.addEventListener("DOMContentLoaded", function () {
            if (typeof window.refreshData === "function") {
                window.refreshData(<?= json_encode($_SESSION["username"]) ?>);
            } else {
                console.error("refreshData wurde nicht geladen.");
            }
        });
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

           <?php if ($lockedCount > 0 || $previewMembers): ?>
                <div class="hero-community-status">

                    <?php if ($previewMembers): ?>
                        <div class="hero-avatars">
                            <?php foreach ($previewMembers as $pm): ?>
                                <a class="hero-avatar<?= (int) $pm['is_locked'] === 1 ? ' is-locked' : '' ?>"
                                href="community.php?m=<?= (int) $pm['id'] ?>"
                                title="<?= htmlspecialchars($pm['username'], ENT_QUOTES, 'UTF-8') ?>"
                                aria-label="View profile of <?= htmlspecialchars($pm['username'], ENT_QUOTES, 'UTF-8') ?>">
                                    <img src="<?= htmlspecialchars($pm['avatar_path'], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
                                </a>
                            <?php endforeach; ?>
                            <?php if ($publicCount > count($previewMembers)): ?>
                                <a class="hero-avatar hero-avatar-more" href="community.php"
                                aria-label="Show all members">+<?= (int) ($publicCount - count($previewMembers)) ?></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                   <a class="hero-community-text" href="community.php">
                        <?php if ($lockedCount > 0): ?>
                            <span class="hero-community-count"><?= (int) $lockedCount ?></span>
                            users are currently locked.
                        <?php else: ?>
                            Meet the community.
                        <?php endif; ?>
                    </a>

                    <a class="hero-community-next" href="register_page.php">Be the next one.</a>

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