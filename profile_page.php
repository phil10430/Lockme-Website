<?php
session_start();

require_once __DIR__ . '/includes/config.php';
require "profile.php";

$bodyClass = "";

require_once __DIR__ . '/templates/header.php';


$username = $user['username'];
$profileVisibility = (($user['profile_visibility'] ?? 'private') === 'public') ? 'public' : 'private';
$stmt = $pdo->prepare('SELECT pro_version FROM users WHERE id=?');
$stmt->execute([$user_id]);
$proVersion = (bool)$stmt->fetchColumn();
$stmt = $pdo->prepare('SELECT box_id, registered_at FROM user_boxes WHERE user_id=? ORDER BY registered_at DESC');
$stmt->execute([$user_id]);
$registeredBoxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="settings-wrapper">

    <div class="settings-container">

        <!-- HEADER -->

        <div class="settings-hero">
            <div class="profile-hero-avatar">
                <?php if (!empty($user['avatar_path'])): ?>
                    <img src="<?= htmlspecialchars($user['avatar_path'], ENT_QUOTES) ?>" alt="Profile avatar" class="box-list-avatar" style="width:72px;height:72px;object-fit:cover;border-radius:50%;">
                <?php else: ?><div class="box-list-avatar-placeholder" style="width:72px;height:72px;border-radius:50%;">👤</div><?php endif; ?>
            </div>

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

  
        <!-- USER PROFILE -->
        <div class="settings-card">
            <div class="settings-card-header profile-heading">
                <span>My Profile</span>
                <span id="profileVisibilityBadge" class="profile-visibility-badge <?= $profileVisibility === 'public' ? 'is-public' : 'is-private' ?>" aria-label="Profile visibility: <?= $profileVisibility ?>">
                    <svg id="profileVisibilityIcon" viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                        <circle cx="12" cy="12" r="3"/>
                        <path id="profileVisibilitySlash" d="M3 3l18 18" <?= $profileVisibility === 'public' ? 'style="display:none"' : '' ?>/>
                    </svg>
                    <span id="profileVisibilityLabel"><?= $profileVisibility === 'public' ? 'Public' : 'Private' ?></span>
                </span>
            </div>
            <div class="settings-card-body">
                <button type="button" class="btn-modern" onclick="document.getElementById('userDetailsDialog').showModal()">Edit Profile</button>
            </div>
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

                       
                            <div class="box-item" data-box-id="<?= htmlspecialchars((string)$bid, ENT_QUOTES) ?>">

                                <div class="box-item-main">

                                    <!-- =====================================================
                                        BOX HEADER
                                    ====================================================== -->

                                    <div class="box-title-row">


                                        <div class="box-title-content">

                                            <span class="box-id">
                                                LockMeBox <?= htmlspecialchars($bid) ?>
                                            </span>



                                        </div>

                                    </div>


                                    <!-- =====================================================
                                        RELATIONSHIP
                                    ====================================================== -->

                                    <?php if (true): ?>

                                      <?php if ($isLocked): ?>

                                        <div class="box-lock-relation">

                                            <span class="lockee-name">
                                                <?= htmlspecialchars($username) ?>
                                            </span>

                                            <?php if (!empty($d['name_top'])): ?>
                                                <span class="relation-text">
                                                    is locked by
                                                </span>

                                                <span class="keyholder-name">
                                                    <?= htmlspecialchars($d['name_top']) ?>
                                                </span>
                                            <?php endif; ?>

                                        </div>

                                    <?php endif; ?>

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

<dialog id="userDetailsDialog" class="modern-dialog">
    <h3>Edit Profile</h3>
    <form id="userDetailsForm" enctype="multipart/form-data">
        <div class="avatar-upload-row">
            <img id="userAvatarPreview" class="avatar-preview" alt="Profile avatar" src="<?= htmlspecialchars($user['avatar_path'] ?? '', ENT_QUOTES) ?>" style="<?= empty($user['avatar_path'])?'display:none;':'' ?>">
            <label class="btn-modern avatar-upload-btn">Upload Avatar
                <input type="file" name="avatar" id="userAvatar" accept="image/jpeg,image/png,image/webp" style="display:none;">
            </label>
        </div>
        <div class="input-group"><input type="text" name="username" id="profileUsername" value="<?= htmlspecialchars($username, ENT_QUOTES) ?>" required maxlength="50" placeholder=" "><label for="profileUsername">Username</label></div>
        <div class="input-group"><input type="date" name="birth_date" id="profileBirthDate" value="<?= htmlspecialchars($user['birth_date'] ?? '', ENT_QUOTES) ?>"><label for="profileBirthDate">Date of birth</label></div>
        <div class="input-group"><input type="url" name="social_x" id="profileSocialX" value="<?= htmlspecialchars($user['social_x'] ?? '', ENT_QUOTES) ?>" maxlength="255" placeholder=" "><label for="profileSocialX">X profile URL</label></div>
        <div class="input-group"><input type="url" name="social_instagram" id="profileSocialInstagram" value="<?= htmlspecialchars($user['social_instagram'] ?? '', ENT_QUOTES) ?>" maxlength="255" placeholder=" "><label for="profileSocialInstagram">Instagram profile URL</label></div>
        <div class="input-group"><input type="url" name="social_bluesky" id="profileSocialBluesky" value="<?= htmlspecialchars($user['social_bluesky'] ?? '', ENT_QUOTES) ?>" maxlength="255" placeholder=" "><label for="profileSocialBluesky">Bluesky profile URL</label></div>
        <div class="profile-visibility-setting">
            <div class="profile-visibility-info">
                <label for="profileVisibilityToggle">
                    Profile visibility
                </label>
                <small>
                    Allow other users to view your public profile.
                </small>
            </div>

            <label class="switch">
                <input
                    type="checkbox"
                    id="profileVisibilityToggle"
                    name="profile_visibility_toggle"
                    value="1"
                    <?= $profileVisibility === 'public' ? 'checked' : '' ?>
                >
                <span class="slider round"></span>
            </label>

            <input
                type="hidden"
                id="profileVisibilityValue"
                name="profile_visibility"
                value="<?= htmlspecialchars($profileVisibility, ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>
        <div id="userDetailsError" class="alert alert-danger" style="display:none"></div>
        <div class="dialog-actions"><button type="button" class="btn-modern" onclick="document.getElementById('userDetailsDialog').close()">Cancel</button><button type="submit" class="btn-modern">Save</button></div>
                                            
    </form>
</dialog>

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
            <input type="text" id="detailsNameTop" name="name_top" placeholder=" " maxlength="255" required>
            <label for="detailsNameTop">Keyholder name</label>
        </div>
        <div class="input-group">
            <input type="text" id="detailsBoxContent" name="box_content" placeholder=" " maxlength="255">
            <label for="detailsBoxContent">Keys for</label>
        </div>
        <div
            id="detailsError"
            class="alert alert-danger"
            style="display:none;"
        ></div>

        <div class="dialog-actions">
            <button
                type="button"
                class="btn-modern"
                onclick="document.getElementById('boxDetailsDialog').close()"
            >
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
        Your profile is private. Change profile visibility under Edit Profile to make it public.
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
<script>
window.boxDetails = <?= json_encode($boxDetails, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
window.boxHistoryData = <?= json_encode($boxHistory, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
window.profileVisibility = <?= json_encode($profileVisibility, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
</script>

<style>
.profile-heading { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.profile-visibility-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; white-space:nowrap; }
.profile-visibility-badge.is-public { color:#4ade80; background:rgba(74,222,128,.12); }
.profile-visibility-badge.is-private { color:#fbbf24; background:rgba(251,191,36,.12); }
</style>
<script>
(function () {
    const dialog = document.getElementById('userDetailsDialog');
    const toggle = document.getElementById('profileVisibilityToggle');
    const hidden = document.getElementById('profileVisibilityValue');
    const badge = document.getElementById('profileVisibilityBadge');
    const label = document.getElementById('profileVisibilityLabel');
    const slash = document.getElementById('profileVisibilitySlash');
    if (!dialog || !toggle || !hidden) return;

    function syncDialog() {
        const visibility = window.profileVisibility === 'public' ? 'public' : 'private';
        toggle.checked = visibility === 'public';
        hidden.value = visibility;
    }

    toggle.addEventListener('change', function () {
        hidden.value = toggle.checked ? 'public' : 'private';
    });
    dialog.addEventListener('close', syncDialog);
    // showModal() triggers no native open event; watch the open attribute.
    new MutationObserver(function () {
        if (dialog.open) syncDialog();
    }).observe(dialog, { attributes: true, attributeFilter: ['open'] });

    window.updateProfileVisibilityBadge = function (visibility) {
        const normalized = visibility === 'public' ? 'public' : 'private';
        window.profileVisibility = normalized;
        if (badge) {
            badge.classList.toggle('is-public', normalized === 'public');
            badge.classList.toggle('is-private', normalized !== 'public');
            badge.setAttribute('aria-label', 'Profile visibility: ' + normalized);
        }
        if (label) label.textContent = normalized === 'public' ? 'Public' : 'Private';
        if (slash) slash.style.display = normalized === 'public' ? 'none' : '';
        syncDialog();
    };
    syncDialog();
})();
</script>
<?php require_once __DIR__ . '/templates/footer.php'; ?>