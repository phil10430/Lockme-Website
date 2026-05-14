<?php
session_start();

require_once __DIR__ . '/includes/config.php';
require "profile.php";

$bodyClass = "";

require_once __DIR__ . '/templates/header.php';


$username = $_SESSION["username"];
$sql = "SELECT * FROM users WHERE username = :username";
$stmt = $pdo->prepare($sql);
$stmt->execute([':username' => $username]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$proVersion = $row['pro_version'];
?>

<div class="settings-wrapper">

    <div class="settings-container">

        <!-- HEADER -->

        <div class="settings-hero">

            <div>
                <h1>
                    <?php echo htmlspecialchars($username); ?>
                </h1>

                <p>
                    Member since
                    <?php echo date("d.m.Y", strtotime($user['created_at'])); ?>
                </p>
            </div>

            <!-- PRO STATUS -->

            <?php if ($proVersion): ?>

                <div class="pro-status active">
                    <span>PRO</span>
                </div>

            <?php else: ?>

                <div class="pro-status">
                    <span>FREE</span>
                </div>

            <?php endif; ?>

        </div>

        <!-- SECURITY -->

        <div class="settings-card">

            <div class="settings-card-header">
                Security
            </div>

            <div class="settings-card-body">

                <button
                    class="btn-modern"
                    onclick="document.getElementById('passwordDialog').showModal()">

                    Change Password

                </button>

            </div>

        </div>

        <!-- EMAIL -->

        <div class="settings-card">

            <div class="settings-card-header">
                E-Mail
            </div>

            <div class="settings-card-body">

                <p class="settings-muted">
                    <?php echo htmlspecialchars($user['email']); ?>
                </p>

                <button
                    class="btn-modern"
                    onclick="document.getElementById('emailDialog').showModal()">

                    Change E-Mail

                </button>

            </div>

        </div>

        <!-- DANGER ZONE -->

        <div class="settings-card danger-zone">

            <div class="settings-card-header">
                Danger Zone
            </div>

            <div class="settings-card-body">

                <button
                    class="btn-danger-modern"
                    onclick="document.getElementById('deleteDialog').showModal()">

                    Delete Account

                </button>

            </div>

        </div>

    </div>

</div>

<!-- =========================
     PASSWORD DIALOG
========================= -->

<dialog id="passwordDialog" class="modern-dialog">

    <h3>Change Password</h3>

    <?php if (!empty($errors['password'])): ?>
        <div class="alert alert-danger">
            <?php echo $errors['password']; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success['password'])): ?>
        <div class="alert alert-success">
            <?php echo $success['password']; ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <div class="input-group">
            <input type="password"
                   name="current_password"
                   placeholder=" "
                   required>
            <label>Current Password</label>
        </div>

        <div class="input-group">
            <input type="password"
                   name="new_password"
                   placeholder=" "
                   required>
            <label>New Password</label>
        </div>

        <div class="input-group">
            <input type="password"
                   name="confirm_new_password"
                   placeholder=" "
                   required>
            <label>Confirm Password</label>
        </div>

        <div class="dialog-actions">

            <button
                type="button"
                class="btn-modern"
                onclick="document.getElementById('passwordDialog').close()">

                Cancel

            </button>

            <button
                type="submit"
                name="changePassword"
                class="btn-modern">

                Save

            </button>

        </div>

    </form>

</dialog>

<!-- =========================
     EMAIL DIALOG
========================= -->

<dialog id="emailDialog" class="modern-dialog">

    <h3>Change E-Mail</h3>

    <?php if (!empty($errors['email'])): ?>
        <div class="alert alert-danger">
            <?php echo $errors['email']; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success['email'])): ?>
        <div class="alert alert-success">
            <?php echo $success['email']; ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <div class="input-group">
            <input type="email"
                   name="new_email"
                   placeholder=" "
                   required>
            <label>New E-Mail</label>
        </div>

        <div class="input-group">
            <input type="password"
                   name="email_password"
                   placeholder=" "
                   required>
            <label>Confirm Password</label>
        </div>

        <div class="dialog-actions">

            <button
                type="button"
                class="btn-modern"
                onclick="document.getElementById('emailDialog').close()">

                Cancel

            </button>

            <button
                type="submit"
                name="changeEmail"
                class="btn-modern">

                Save

            </button>

        </div>

    </form>

</dialog>

<!-- =========================
     DELETE DIALOG
========================= -->

<dialog id="deleteDialog" class="modern-dialog">

    <h3>Delete Account?</h3>

    <p class="settings-muted">
        This action cannot be undone.
    </p>

    <?php if (!empty($errors['delete'])): ?>
        <div class="alert alert-danger">
            <?php echo $errors['delete']; ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <div class="input-group">
            <input type="password"
                   name="delete_password"
                   placeholder=" "
                   required>
            <label>Confirm Password</label>
        </div>

        <div class="dialog-actions">

            <button
                type="button"
                class="btn-modern"
                onclick="document.getElementById('deleteDialog').close()">

                Cancel

            </button>

            <button
                type="submit"
                name="deleteAccount"
                class="btn-danger-modern">

                Delete

            </button>

        </div>

    </form>

</dialog>

<?php require_once __DIR__ . '/templates/footer.php'; ?>