let currentDetailsBoxId = null;


// =========================================================
// OPEN BOX DETAILS DIALOG
// =========================================================

function openBoxDetailsDialog(boxId) {

    currentDetailsBoxId = boxId;

    const d =
        (window.boxDetails &&
         window.boxDetails[boxId])
            || {};


    // -----------------------------------------------------
    // Box name
    // -----------------------------------------------------

    document.getElementById(
        'detailsBoxName'
    ).textContent = boxId;


    // -----------------------------------------------------
    // Text fields
    // -----------------------------------------------------

    document.getElementById(
        'detailsNameTop'
    ).value = d.name_top || '';

    document.getElementById(
        'detailsNameSub'
    ).value = d.name_sub || '';

    document.getElementById(
        'detailsBoxContent'
    ).value = d.box_content || '';


    // -----------------------------------------------------
    // Target date
    // -----------------------------------------------------

    document.getElementById(
        'detailsTargetDate'
    ).value =
        d.target_open_date
            ? d.target_open_date
                .replace(' ', 'T')
                .slice(0, 16)
            : '';


    // -----------------------------------------------------
    // Avatar
    // -----------------------------------------------------

    const preview =
        document.getElementById(
            'detailsAvatarPreview'
        );

    const placeholder =
        document.getElementById(
            'detailsAvatarPlaceholder'
        );

    const avatarInput =
        document.getElementById(
            'detailsAvatar'
        );


    // Reset file input

    if (avatarInput) {
        avatarInput.value = '';
    }


    // Existing avatar

    if (d.avatar_path) {

        preview.src =
            d.avatar_path;

        preview.style.display =
            'block';

        if (placeholder) {
            placeholder.style.display =
                'none';
        }

    } else {

        preview.src = '';

        preview.style.display =
            'none';

        if (placeholder) {
            placeholder.style.display =
                'block';
        }
    }


    // -----------------------------------------------------
    // Reset error
    // -----------------------------------------------------

    document.getElementById(
        'detailsError'
    ).style.display = 'none';


    // -----------------------------------------------------
    // Open dialog
    // -----------------------------------------------------

    document.getElementById(
        'boxDetailsDialog'
    ).showModal();
}


// =========================================================
// DOM READY
// =========================================================

document.addEventListener(
    'DOMContentLoaded',
    function () {


        // =================================================
        // AVATAR PREVIEW
        // =================================================

        const avatarInput =
            document.getElementById(
                'detailsAvatar'
            );

        const avatarPreview =
            document.getElementById(
                'detailsAvatarPreview'
            );

        const avatarPlaceholder =
            document.getElementById(
                'detailsAvatarPlaceholder'
            );


        if (avatarInput) {

            avatarInput.addEventListener(
                'change',
                function () {

                    const file =
                        this.files[0];

                    if (!file) {
                        return;
                    }


                    // -------------------------------------
                    // Client-side MIME check
                    // -------------------------------------

                    const allowedTypes = [
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ];

                    if (
                        !allowedTypes.includes(
                            file.type
                        )
                    ) {

                        this.value = '';

                        alert(
                            'Only JPG, PNG and WebP images are allowed.'
                        );

                        return;
                    }


                    // -------------------------------------
                    // Client-side size check
                    // -------------------------------------

                    if (
                        file.size >
                        2 * 1024 * 1024
                    ) {

                        this.value = '';

                        alert(
                            'Avatar must not be larger than 2 MB.'
                        );

                        return;
                    }


                    // -------------------------------------
                    // Create local preview
                    // -------------------------------------

                    const reader =
                        new FileReader();

                    reader.onload =
                        function (e) {

                            avatarPreview.src =
                                e.target.result;

                            avatarPreview.style.display =
                                'block';

                            if (avatarPlaceholder) {
                                avatarPlaceholder.style.display =
                                    'none';
                            }
                        };

                    reader.readAsDataURL(file);
                }
            );
        }


        // =================================================
        // BOX DETAILS FORM
        // =================================================

        const form =
            document.getElementById(
                'boxDetailsForm'
            );

        if (!form) {
            return;
        }


        form.addEventListener(
            'submit',
            function (e) {

                e.preventDefault();


                const errorBox =
                    document.getElementById(
                        'detailsError'
                    );

                errorBox.style.display =
                    'none';


                // -----------------------------------------
                // FormData
                // -----------------------------------------

                const formData =
                    new FormData();


                formData.append(
                    'saveBoxDetails',
                    '1'
                );

                formData.append(
                    'boxId',
                    currentDetailsBoxId
                );

                formData.append(
                    'name_top',
                    document.getElementById(
                        'detailsNameTop'
                    ).value
                );

                formData.append(
                    'name_sub',
                    document.getElementById(
                        'detailsNameSub'
                    ).value
                );

                formData.append(
                    'box_content',
                    document.getElementById(
                        'detailsBoxContent'
                    ).value
                );

                formData.append(
                    'target_open_date',
                    document.getElementById(
                        'detailsTargetDate'
                    ).value
                );


                // -----------------------------------------
                // Avatar
                // -----------------------------------------

                const avatarInput =
                    document.getElementById(
                        'detailsAvatar'
                    );

                if (
                    avatarInput &&
                    avatarInput.files.length > 0
                ) {

                    formData.append(
                        'avatar',
                        avatarInput.files[0]
                    );
                }


                // -----------------------------------------
                // Disable save button while uploading
                // -----------------------------------------

                const submitButton =
                    form.querySelector(
                        'button[type="submit"]'
                    );

                const originalButtonText =
                    submitButton
                        ? submitButton.textContent
                        : '';

                if (submitButton) {

                    submitButton.disabled =
                        true;

                    submitButton.textContent =
                        'Saving...';
                }


                // -----------------------------------------
                // Send request
                // -----------------------------------------

                fetch('', {
                    method: 'POST',
                    body: formData
                })

                .then(function (res) {

                    if (!res.ok) {
                        throw new Error(
                            'HTTP ' + res.status
                        );
                    }

                    return res.json();
                })

                .then(function (data) {

                    if (data.success) {

                        location.reload();

                    } else {

                        errorBox.textContent =
                            data.message ||
                            'Could not save details.';

                        errorBox.style.display =
                            'block';
                    }
                })

                .catch(function (error) {

                    console.error(
                        'Box details error:',
                        error
                    );

                    errorBox.textContent =
                        'Network error.';

                    errorBox.style.display =
                        'block';
                })

                .finally(function () {

                    if (submitButton) {

                        submitButton.disabled =
                            false;

                        submitButton.textContent =
                            originalButtonText;
                    }
                });
            }
        );
    }
);


// =========================================================
// SHARE DIALOG
// =========================================================

function openShareDialog(boxId) {

    const url =
        window.location.origin +
        '/box_share.php?box_id=' +
        encodeURIComponent(boxId);

    const text =
        'My LockMeBox status:';


    document.getElementById(
        'shareBoxName'
    ).textContent = boxId;


    document.getElementById(
        'shareLinkInput'
    ).value = url;


    document.getElementById(
        'shareCopiedMsg'
    ).style.display = 'none';


    document.getElementById(
        'shareTwitterLink'
    ).href =
        'https://twitter.com/intent/tweet?url=' +
        encodeURIComponent(url) +
        '&text=' +
        encodeURIComponent(text);


    document.getElementById(
        'shareBlueskyLink'
    ).href =
        'https://bsky.app/intent/compose?text=' +
        encodeURIComponent(
            text + ' ' + url
        );


    document.getElementById(
        'shareDialog'
    ).showModal();
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