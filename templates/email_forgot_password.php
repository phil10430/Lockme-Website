<?php
ob_start();
?>

<p>Hello <strong><?= htmlspecialchars($user['username']) ?></strong>,</p>

<p>
    We received a request to reset your password.
</p>

<p style="margin:30px 0; text-align:center;">
    <a href="<?= htmlspecialchars($mlink) ?>"
       style="
            display:inline-block;
            padding:12px 22px;
            background:#4CAF50;
            color:#ffffff;
            text-decoration:none;
            border-radius:8px;
            font-weight:bold;
       ">
        Reset Password
    </a>
</p>

<p>
    If the button above does not work, copy and paste this link into your browser:
</p>

<p>
    <a href="<?= htmlspecialchars($mlink) ?>">
        <?= htmlspecialchars($mlink) ?>
    </a>
</p>

<p style="margin-top:25px;">
    If you did not request a password reset, you can safely ignore this email.
</p>

<?php
$content = ob_get_clean();

$title = "Password Reset";

include __DIR__ . "/email_base.php";

