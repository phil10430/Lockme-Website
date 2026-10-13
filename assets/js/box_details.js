let currentDetailsBoxId = null;


// =========================================================
// OPEN BOX DETAILS DIALOG
// =========================================================


function openBoxDetailsDialog(boxId) {
    currentDetailsBoxId = boxId;
    const d = (window.boxDetails && window.boxDetails[boxId]) || {};

    document.getElementById('detailsBoxName').textContent = boxId;
    document.getElementById('detailsNameTop').value = d.name_top || '';
    document.getElementById('detailsNameSub').value = d.name_sub || '';
    document.getElementById('detailsBoxContent').value = d.box_content || '';

    document.getElementById('detailsAvatar').value = '';

    document.getElementById('detailsPublicStatus').checked =
        Number(d.public_status) === 1;

    // Load the Lockee's social profile URLs.
    document.getElementById('detailsSocialX').value =
        d.social_x || '';

    document.getElementById('detailsSocialInstagram').value =
        d.social_instagram || '';

    document.getElementById('detailsSocialBluesky').value =
        d.social_bluesky || '';

    const preview = document.getElementById('detailsAvatarPreview');
    const placeholder = document.getElementById('detailsAvatarPlaceholder');

    if (d.avatar_path) {
        preview.src = '/' + d.avatar_path;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
    } else {
        preview.src = '';
        preview.style.display = 'none';
        placeholder.style.display = 'block';
    }

    document.getElementById('detailsError').style.display = 'none';
    document.getElementById('boxDetailsDialog').showModal();
}

// =========================================================
// DOM READY
// =========================================================

document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // AVATAR PREVIEW
    // =====================================================

    const avatarInput = document.getElementById('detailsAvatar');
    const avatarPreview = document.getElementById('detailsAvatarPreview');
    const avatarPlaceholder = document.getElementById('detailsAvatarPlaceholder');

    if (avatarInput) {
        avatarInput.addEventListener('change', function () {

            const file = this.files[0];

            if (!file) {
                return;
            }

            // ---------------------------------------------
            // Client-side MIME check
            // ---------------------------------------------

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!allowedTypes.includes(file.type)) {
                this.value = '';

                alert('Only JPG, PNG and WebP images are allowed.');

                return;
            }

            // ---------------------------------------------
            // Client-side size check
            // ---------------------------------------------

            if (file.size > 2 * 1024 * 1024) {
                this.value = '';

                alert('Avatar must not be larger than 2 MB.');

                return;
            }

            // ---------------------------------------------
            // Create local preview
            // ---------------------------------------------

            const reader = new FileReader();

            reader.onload = function (e) {

                if (avatarPreview) {
                    avatarPreview.src = e.target.result;
                    avatarPreview.style.display = 'block';
                }

                if (avatarPlaceholder) {
                    avatarPlaceholder.style.display = 'none';
                }
            };

            reader.readAsDataURL(file);
        });
    }


    // =====================================================
    // BOX DETAILS FORM
    // =====================================================

    const form = document.getElementById('boxDetailsForm');

    if (!form) {
        return;
    }

    form.addEventListener('submit', function (e) {

        e.preventDefault();

        const errorBox = document.getElementById('detailsError');

        if (errorBox) {
            errorBox.style.display = 'none';
            errorBox.textContent = '';
        }

        // ---------------------------------------------
        // Validate required elements
        // ---------------------------------------------

        const nameTopInput = document.getElementById('detailsNameTop');
        const nameSubInput = document.getElementById('detailsNameSub');
        const boxContentInput = document.getElementById('detailsBoxContent');
        const publicStatusInput = document.getElementById('detailsPublicStatus');

        const socialXInput = document.getElementById('detailsSocialX');
        const socialInstagramInput = document.getElementById('detailsSocialInstagram');
        const socialBlueskyInput = document.getElementById('detailsSocialBluesky');

        if (
            !nameTopInput ||
            !nameSubInput ||
            !boxContentInput ||
            !publicStatusInput ||
            !socialXInput ||
            !socialInstagramInput ||
            !socialBlueskyInput
        ) {
            if (errorBox) {
                errorBox.textContent =
                    'One or more form fields could not be found. Please check the HTML element IDs.';
                errorBox.style.display = 'block';
            }

            return;
        }

        if (
            typeof currentDetailsBoxId === 'undefined' ||
            currentDetailsBoxId === null ||
            currentDetailsBoxId === ''
        ) {
            if (errorBox) {
                errorBox.textContent = 'No box selected.';
                errorBox.style.display = 'block';
            }

            return;
        }


        // ---------------------------------------------
        // Build FormData
        // ---------------------------------------------

        const formData = new FormData();

        formData.append('saveBoxDetails', '1');
        formData.append('boxId', currentDetailsBoxId);

        formData.append('name_top', nameTopInput.value);
        formData.append('name_sub', nameSubInput.value);
        formData.append('box_content', boxContentInput.value);

        formData.append(
            'public_status',
            publicStatusInput.checked ? '1' : '0'
        );


        // ---------------------------------------------
        // Lockee social media profiles
        // ---------------------------------------------

        formData.append(
            'social_x',
            socialXInput.value.trim()
        );

        formData.append(
            'social_instagram',
            socialInstagramInput.value.trim()
        );

        formData.append(
            'social_bluesky',
            socialBlueskyInput.value.trim()
        );


        // ---------------------------------------------
        // Avatar upload
        // ---------------------------------------------

        if (avatarInput && avatarInput.files.length > 0) {
            formData.append(
                'avatar',
                avatarInput.files[0]
            );
        }


        // ---------------------------------------------
        // Disable save button while uploading
        // ---------------------------------------------

        const submitButton = form.querySelector('button[type="submit"]');

        const originalButtonText = submitButton
            ? submitButton.textContent
            : '';

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Saving...';
        }


        // ---------------------------------------------
        // Send request
        // ---------------------------------------------

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })

        .then(function (res) {

            if (!res.ok) {
                throw new Error('HTTP ' + res.status);
            }

            return res.json();
        })

        .then(function (data) {

            if (data.success) {

                // Reload to display the saved data.
                window.location.reload();

            } else {

                if (errorBox) {
                    errorBox.textContent =
                        data.message || 'Could not save details.';

                    errorBox.style.display = 'block';
                }
            }
        })

        .catch(function (error) {

            console.error('Box details error:', error);

            if (errorBox) {
                errorBox.textContent =
                    'Could not save the details. Please try again.';

                errorBox.style.display = 'block';
            }
        })

        .finally(function () {

            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            }
        });
    });
});


// =========================================================
// SHARE DIALOG
// =========================================================

function openShareDialog(boxId) {
    const url = window.location.origin + '/box_share.php?box_id=' + encodeURIComponent(boxId);
    const text = 'My LockMeBox status:';
    const d = (window.boxDetails && window.boxDetails[boxId]) || {};

    document.getElementById('shareBoxName').textContent = boxId;
    document.getElementById('shareLinkInput').value = url;
    document.getElementById('shareCopiedMsg').style.display = 'none';

    document.getElementById('sharePublicWarning').style.display =
        (d.public_status && Number(d.public_status) === 1) ? 'none' : 'block';

    document.getElementById('shareTwitterLink').href =
        'https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(text);

    document.getElementById('shareBlueskyLink').href =
        'https://bsky.app/intent/compose?text=' + encodeURIComponent(text + ' ' + url);

    document.getElementById('shareDialog').showModal();
}


// =========================================================
// COPY SHARE LINK
// =========================================================

function copyShareLink() {

    const input =
        document.getElementById(
            'shareLinkInput'
        );

    input.select();


    if (
        navigator.clipboard &&
        navigator.clipboard.writeText
    ) {

        navigator.clipboard.writeText(
            input.value
        )
        .then(function () {

            document.getElementById(
                'shareCopiedMsg'
            ).style.display =
                'block';

        })
        .catch(function () {

            fallbackCopyShareLink();
        });

    } else {

        fallbackCopyShareLink();
    }
}


// =========================================================
// FALLBACK COPY
// =========================================================

function fallbackCopyShareLink() {

    try {

        document.execCommand('copy');

    } catch (e) {

        console.error(
            'Copy failed:',
            e
        );
    }

    document.getElementById(
        'shareCopiedMsg'
    ).style.display =
        'block';
}