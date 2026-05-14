<?php
ob_start();
?>

<p>Hello <strong><?= htmlspecialchars($username) ?></strong>,</p>

<p>Please verify your email address to activate your account.</p>

<p>
    <a href="<?= htmlspecialchars($verify_link) ?>">
        Verify Email
    </a>
</p>

<p style="margin-top:20px;">
    If the button doesn't work, copy and paste this link:<br>
    <a href="<?= $verify_link ?>"><?= $verify_link ?></a>
</p>

<?php
$content = ob_get_clean();
$title = "Confirm your registration";
include __DIR__ . "/email_base.php";