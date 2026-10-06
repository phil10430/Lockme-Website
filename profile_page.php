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
$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
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

                    <p class="box-empty">
                        No boxes registered yet.
                    </p>

                <?php else: ?>

                    <div class="box-list">

                        <?php foreach (
                            $registeredBoxes as $box
                        ):

                            $bid =
                                $box['box_id'];

                            $d =
                                $boxDetails[$bid]
                                ?? null;

                          
                            $actual = $boxActual[$bid] ?? null;
                            $isLocked = $actual && $actual['effectively_locked'];

                        ?>

                       
                            <div class="box-item">

                                <div class="box-item-main">

                                    <!-- =====================================================
                                        BOX HEADER
                                    ====================================================== -->

                                    <div class="box-title-row">

                                        <?php if ($d && !empty($d['avatar_path'])): ?>

                                            <img
                                                class="box-list-avatar"
                                                src="<?= htmlspecialchars($d['avatar_path']) ?>"
                                                alt=""
                                                loading="lazy"
                                            >

                                        <?php else: ?>

                                            <div
                                                class="box-list-avatar-placeholder"
                                                aria-hidden="true">
                                            </div>

                                        <?php endif; ?>


                                        <div class="box-title-content">

                                            <span class="box-id">
                                                LockMeBox <?= htmlspecialchars($bid) ?>
                                            </span>


                                            <?php if ($d): ?>

                                                <span
                                                    class="box-public-badge"
                                                    title="<?= (int)($d['public_status'] ?? 0) === 1
                                                        ? 'Profile is publicly visible'
                                                        : 'Profile is not public' ?>"
                                                    aria-label="<?= (int)($d['public_status'] ?? 0) === 1
                                                        ? 'Public profile'
                                                        : 'Private profile' ?>">

                                                    <svg
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        aria-hidden="true">

                                                        <?php if ((int)($d['public_status'] ?? 0) === 1): ?>

                                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>

                                                        <?php else: ?>

                                                            <path d="M3 3l18 18"></path>
                                                            <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                                                            <path d="M9.9 5.2A11.4 11.4 0 0 1 12 5c6.4 0 10 7 10 7a15.7 15.7 0 0 1-3 3.8"></path>
                                                            <path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.1 0 2.1-.2 3-.5"></path>

                                                        <?php endif; ?>

                                                    </svg>

                                                </span>

                                            <?php endif; ?>

                                        </div>

                                    </div>


                                    <!-- =====================================================
                                        RELATIONSHIP
                                    ====================================================== -->

                                    <?php if ($d && (!empty($d['name_top']) || !empty($d['name_sub']))): ?>

                                        <div class="box-lock-relation">

                                            <?php if (!empty($d['name_sub'])): ?>

                                                <span class="lockee-name">
                                                    <?= htmlspecialchars($d['name_sub']) ?>
                                                </span>

                                            <?php endif; ?>


                                            <?php if (!empty($d['name_sub']) && !empty($d['name_top'])): ?>

                                                <span class="relation-text">
                                                    is locked by
                                                </span>

                                            <?php endif; ?>


                                            <?php if (!empty($d['name_top'])): ?>

                                                <span class="keyholder-name">
                                                    <?= htmlspecialchars($d['name_top']) ?>
                                                </span>

                                            <?php endif; ?>

                                        </div>

                                    <?php endif; ?>


                                    <!-- =====================================================
                                        BOX CONTENT / KEY
                                    ====================================================== -->

                                    <?php if ($d && !empty($d['box_content'])): ?>

                                        <div class="box-content-tag">

                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true">

                                                <circle
                                                    cx="7.5"
                                                    cy="15.5"
                                                    r="5.5">
                                                </circle>

                                                <path d="m21 2-9.6 9.6"></path>
                                                <path d="m15.5 7.5 3 3"></path>
                                                <path d="m18.5 4.5 3 3"></path>

                                            </svg>


                                            <span>
                                                <?= htmlspecialchars($d['box_content']) ?>
                                            </span>

                                        </div>

                                    <?php endif; ?>


                                    <!-- =====================================================
                                        LOCK STATUS
                                    ====================================================== -->

                                    <div class="box-lock-status">

                                        <?php if ($isLocked && !empty($actual['locked_since'])): ?>

                                            <svg
                                                class="box-lock-icon"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true">

                                                <rect
                                                    x="5"
                                                    y="11"
                                                    width="14"
                                                    height="10"
                                                    rx="2">
                                                </rect>

                                                <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>

                                            </svg>


                                            <span class="box-lock-duration">
                                                Locked since
                                                <?= htmlspecialchars(locked_duration($actual['locked_since'])) ?>
                                            </span>

                                        <?php endif; ?>


                                        <!-- JavaScript status -->
                                        <span
                                            class="box-status"
                                            id="box-status-<?= htmlspecialchars($bid) ?>">
                                        </span>

                                    </div>

                                </div>



                                <!-- =========================================================
                                    ACTIONS
                                ========================================================== -->

                                <div class="box-item-actions">

                                    <div class="box-action-icons">

                                        <!-- History -->
                                        <button
                                            type="button"
                                            class="box-icon-btn"
                                            title="Show History"
                                            aria-label="Show History"
                                            onclick="openHistoryDialog('<?= htmlspecialchars($bid) ?>')">

                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true">

                                                <rect
                                                    x="3"
                                                    y="5"
                                                    width="18"
                                                    height="16"
                                                    rx="2">
                                                </rect>

                                                <path d="M16 3v4"></path>
                                                <path d="M8 3v4"></path>
                                                <path d="M3 11h18"></path>

                                            </svg>

                                        </button>


                                        <!-- Box Details -->
                                        <button
                                            type="button"
                                            class="box-icon-btn"
                                            title="Set Details"
                                            aria-label="Set Details"
                                            onclick="openBoxDetailsDialog('<?= htmlspecialchars($bid) ?>')">

                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true">

                                                <path d="M12 20h9"></path>

                                                <path
                                                    d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z">
                                                </path>

                                            </svg>

                                        </button>


                                        <!-- Share -->
                                        <button
                                            type="button"
                                            class="box-icon-btn"
                                            title="Share Status"
                                            aria-label="Share Status"
                                            onclick="openShareDialog('<?= htmlspecialchars($bid) ?>')">

                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true">

                                                <circle
                                                    cx="18"
                                                    cy="5"
                                                    r="3">
                                                </circle>

                                                <circle
                                                    cx="6"
                                                    cy="12"
                                                    r="3">
                                                </circle>

                                                <circle
                                                    cx="18"
                                                    cy="19"
                                                    r="3">
                                                </circle>

                                                <line
                                                    x1="8.59"
                                                    y1="13.51"
                                                    x2="15.42"
                                                    y2="17.49">
                                                </line>

                                                <line
                                                    x1="15.41"
                                                    y1="6.51"
                                                    x2="8.59"
                                                    y2="10.49">
                                                </line>

                                            </svg>

                                        </button>

                                    </div>


                                    <!-- Remove -->
                                    <button
                                        type="button"
                                        class="box-icon-btn box-icon-btn-danger"
                                        title="Remove Box"
                                        aria-label="Remove Box"
                                        onclick="removeBox('<?= htmlspecialchars($bid) ?>')">

                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            aria-hidden="true">

                                            <polyline points="3 6 5 6 21 6"></polyline>

                                            <path
                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6">
                                            </path>

                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>

                                            <line
                                                x1="10"
                                                y1="11"
                                                x2="10"
                                                y2="17">
                                            </line>

                                            <line
                                                x1="14"
                                                y1="11"
                                                x2="14"
                                                y2="17">
                                            </line>

                                        </svg>

                                    </button>

                                </div>



                            </div>



                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

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

    <form id="boxDetailsForm" enctype="multipart/form-data">

        <div class="avatar-upload-row">
            <img id="detailsAvatarPreview" class="avatar-preview" src="" alt="" style="display:none;">
            <div id="detailsAvatarPlaceholder" class="avatar-placeholder"></div>
            <label class="btn-modern avatar-upload-btn">
                Upload Image
                <input type="file" id="detailsAvatar" name="avatar" accept="image/png, image/jpeg, image/webp" style="display:none;">
            </label>
        </div>

        <div class="input-group">
            <input type="text" id="detailsNameTop" name="name_top" placeholder=" " required>
            <label>Keyholder name</label>
        </div>

        <div class="input-group">
            <input type="text" id="detailsNameSub" name="name_sub" placeholder=" ">
            <label>Lockee name</label>
        </div>

        <div class="input-group">
            <input type="text" id="detailsBoxContent" name="box_content" placeholder=" ">
            <label>Keys for</label>
        </div>

        <label class="toggle-row">
            <span>Show this box's status publicly</span>
            <input type="checkbox" id="detailsPublicStatus" name="public_status_checkbox">
        </label>

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

<!-- =========================
     SHARE DIALOG
========================= -->

<dialog id="shareDialog" class="modern-dialog">

    <h4>Share Status of LockMeBox <span id="shareBoxName"></span></h4>

    <div id="sharePublicWarning" class="alert alert-danger" style="display:none;">
        This box's status is currently private. Enable "Show this box's status publicly" in Set Details so this link actually shows something.
    </div>


    <div class="input-group">
        <input type="text" id="shareLinkInput" readonly onclick="this.select()">
        <label>Link</label>
    </div>

    <div class="share-icon-row">

        <button type="button" class="share-icon-btn" onclick="copyShareLink()" title="Copy link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="9" width="13" height="13" rx="2"></rect>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            </svg>
        </button>

        <a id="shareTwitterLink" class="share-icon-btn" target="_blank" rel="noopener" title="Share on X">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
            </svg>
        </a>

        <a id="shareBlueskyLink" class="share-icon-btn" target="_blank" rel="noopener" title="Share on Bluesky">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 10.8c-.9-1.75-3.36-5.02-5.64-6.63C4.17 2.65 3.3 2.98 2.73 3.3c-.66.38-.83 1.36-.83 1.93 0 .58.32 4.76.53 5.45.7 2.3 3.2 3.08 5.5 2.82-3.98.58-7.5 2.24-2.87 7.4 5.08 5.23 6.96-1.12 7.94-4.49.98 3.37 2.16 9.5 7.91 4.49 4.32-4.49 1.4-6.82-2.58-7.4 2.3.26 4.8-.52 5.5-2.82.21-.69.53-4.87.53-5.45 0-.57-.17-1.55-.83-1.93-.57-.32-1.44-.65-3.63.87-2.28 1.61-4.74 4.88-5.64 6.63z"/>
            </svg>
        </a>

    </div>

    <p id="shareCopiedMsg" class="settings-muted" style="display:none; margin-top:10px;">
        Link copied.
    </p>

    <div class="dialog-actions">
        <button type="button" class="btn-modern" onclick="document.getElementById('shareDialog').close()">
            Close
        </button>
    </div>

</dialog>
<?php require_once __DIR__ . '/templates/footer.php'; ?>