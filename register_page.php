<?php require_once __DIR__ . '/includes/config.php'; ?>
<?php require "register.php"; ?>
<?php require_once __DIR__ . '/templates/header.php';  ?>

        <div class="card">

            <div class="card-header">
                In order to use your LOCKMEBOX you have to sign up first.
            </div>

            <div class="card-body">
                <?php include __DIR__ . '/templates/register_form.php'; ?>
            </div>

            <div class="card-footer">
                Already have an account? <a href="index.php">Login here</a>
            </div>

        </div>

</body>

</html>