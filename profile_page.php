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

// Load user data
$stmt = $pdo->prepare("SELECT username, email, created_at, public_status FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Registrierte Boxen abrufen
$sql = "SELECT box_id, registered_at FROM user_boxes 
        WHERE user_id = :user_id 
        ORDER BY registered_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':user_id' => $row['id']]);
$registeredBoxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

  
        <!-- REGISTERED BOXES -->
        <div class="settings-card">

            <div class="settings-card-header">
                My LockMeBox:
            </div>

            <div class="settings-card-body">

               <?php if (empty($registeredBoxes)): ?>
                    <p class="box-empty">No boxes registered yet.</p>
                <?php else: ?>
                    <div class="box-list">
                        <?php foreach ($registeredBoxes as $box):
                            $bid = $box['box_id'];
                            $d = $boxDetails[$bid] ?? null;
                        ?>
                            <div class="box-item" id="box-<?= htmlspecialchars($bid) ?>">

                               <?php
                                $actual = $boxActual[$bid] ?? null;
                                $isLocked = $actual && (int)$actual['lock_status'] === 1;
                                ?>
                                <div class="box-item-main">
                                    <span class="box-id">LockMeBox <?= htmlspecialchars($bid) ?></span>
                                    <?php if ($isLocked && !empty($actual['locked_since'])): ?>
                                         <div class="box-details-info">
                                            <?php if (!empty($d['name_top'])): ?>
                                                <span>Locker: <?= htmlspecialchars($d['name_top']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($d['name_sub'])): ?>
                                                <span>Lockee: <?= htmlspecialchars($d['name_sub']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($d['box_content'])): ?>
                                                <span>Box Content: <?= htmlspecialchars($d['box_content']) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($d['target_open_date'])): ?>
                                                <span>Target Open Date: <?= htmlspecialchars(date('d.m.Y H:i', strtotime($d['target_open_date']))) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="box-details-info">
                                            <span>Locked since: <?= htmlspecialchars(locked_duration($actual['locked_since'])) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <span class="box-status" id="box-status-<?= htmlspecialchars($bid) ?>"></span>
                                </div>

                                <div class="box-item-actions">

                                    <button type="button" class="box-action-btn" onclick="openHistoryDialog('<?= htmlspecialchars($bid) ?>')">
                                        Show History
                                    </button>

                                    <button type="button" class="box-action-btn" onclick="openBoxDetailsDialog('<?= htmlspecialchars($bid) ?>')">
                                        Set Details
                                    </button>

                                    <button type="button" class="box-action-btn box-remove-btn" onclick="removeBox('<?= htmlspecialchars($bid) ?>')">
                                        Remove Box
                                    </button>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>

        </div>

        <!-- PUBLIC STATUS -->
        <div class="settings-card">

            <div class="settings-card-header">
                Public Status
            </div>

            <div class="settings-card-body">

                <label class="toggle-row">
                    <span>Show my box status publicly</span>
                    <input
                        type="checkbox"
                        id="publicStatusToggle"
                        <?= $user['public_status'] ? 'checked' : '' ?>
                        onchange="togglePublicStatus(this.checked)">
                </label>

            </div>

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
                Account
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

 
<!-- =========================
     REMOVE BOX CONFIRM DIALOG
========================= -->

<dialog id="removeBoxDialog" class="modern-dialog">

    <h3>Remove Box</h3>

    <p>Are you sure you want to remove box <strong id="removeBoxName"></strong>?</p>

    <div class="dialog-actions">

        <button
            type="button"
            class="btn-modern"
            onclick="document.getElementById('removeBoxDialog').close()">

            Cancel

        </button>

        <button
            type="button"
            class="btn-danger-modern"
            id="confirmRemoveBtn">

            Remove

        </button>

    </div>

</dialog>

<!-- =========================
     BOX HISTORY DIALOG
========================= -->

<dialog id="historyDialog" class="modern-dialog history-dialog">

    <h3>History – <span id="historyBoxName"></span></h3>

    <div id="historySummary"></div>

    <div id="historyContent" class="history-timeline">
        <!-- wird per JS befüllt -->
    </div>

    <div class="dialog-actions">
        <button
            type="button"
            class="btn-modern"
            onclick="document.getElementById('historyDialog').close()">
            Close
        </button>
    </div>

</dialog>

<!-- =========================
     BOX DETAILS DIALOG
========================= -->

<dialog id="boxDetailsDialog" class="modern-dialog">

    <h3>Box Details – <span id="detailsBoxName"></span></h3>

    <form id="boxDetailsForm">

        <div class="input-group">
            <input type="text" id="detailsNameTop" name="name_top" placeholder=" " required>
            <label>Name of Locker</label>
        </div>

        <div class="input-group">
            <input type="text" id="detailsNameSub" name="name_sub" placeholder=" ">
            <label>Name of Lockee</label>
        </div>

        <div class="input-group">
            <input type="text" id="detailsBoxContent" name="box_content" placeholder=" ">
            <label>Box Content</label>
        </div>

        <div class="input-group">
            <input type="datetime-local" id="detailsTargetDate" name="target_open_date" placeholder=" ">
            <label>Target Open Date</label>
        </div>

        <div id="detailsError" class="alert alert-danger" style="display:none;"></div>

        <div class="dialog-actions">
            <button type="button" class="btn-modern" onclick="document.getElementById('boxDetailsDialog').close()">
                Cancel
            </button>
            <button type="submit" class="btn-modern">
                Save
            </button>
        </div>

    </form>

</dialog>


<?php require_once __DIR__ . '/templates/footer.php'; ?>