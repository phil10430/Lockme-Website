// Open one profile at a time in a dedicated dialog.
(function () {
    const profileDialog = document.getElementById('profileDialog');
    let activeBoxId = null;

    document.addEventListener('click', function (event) {
        const card = event.target.closest('[data-profile-box]');
        if (!card) return;

        activeBoxId = card.dataset.profileBox;
        const avatarContainer = document.getElementById('profileDialogAvatar');
        avatarContainer.replaceChildren();

        if (card.dataset.profileAvatar) {
            const image = document.createElement('img');
            image.className = 'community-lockee-avatar';
            image.src = card.dataset.profileAvatar;
            image.alt = '';
            avatarContainer.appendChild(image);
        } else {
            const placeholder = document.createElement('div');
            placeholder.className = 'community-lockee-avatar-placeholder';
            placeholder.setAttribute('aria-hidden', 'true');
            avatarContainer.appendChild(placeholder);
        }

        document.getElementById('profileDialogName').textContent = card.dataset.profileName || 'Lockee';
        document.getElementById('profileDialogStatusText').textContent = card.dataset.profileStatus || 'Unknown';
        const status = document.getElementById('profileDialogStatus');
        status.className = 'community-profile-status ' + (card.dataset.profileStatusClass || 'is-unknown');
        document.getElementById('profileDialogKeyholder').textContent = card.dataset.profileKeyholder || 'Not specified';
        document.getElementById('profileDialogContent').textContent = card.dataset.profileContent || 'Not specified';
        document.getElementById('profileDialogDuration').textContent = card.dataset.profileDuration || '—';
        document.getElementById('profileDialogBoxId').textContent = 'LockMeBox #' + activeBoxId;

        // Render monochrome social icons only for defined, valid profile URLs.
        const socialContainer = document.getElementById('profileDialogSocials');
        const socialRow = document.getElementById('profileSocialRow');
        socialContainer.replaceChildren();

        const socialProfiles = [
            {
                url: card.dataset.profileSocialX,
                label: 'X',
                hosts: ['x.com', 'www.x.com', 'twitter.com', 'www.twitter.com'],
                svg: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M18.9 2H22l-6.8 7.8L23.2 22h-6.3L12 14.6 5.5 22H2.4l7.3-8.4L1.8 2h6.5l4.5 6.8L18.9 2Zm-1.1 18h1.7L7.3 3.9H5.5L17.8 20Z"/></svg>'
            },
            {
                url: card.dataset.profileSocialInstagram,
                label: 'Instagram',
                hosts: ['instagram.com', 'www.instagram.com'],
                svg: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect class="social-instagram-outline" x="3" y="3" width="18" height="18" rx="5"/><circle class="social-instagram-outline" cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.8" r="1.2" fill="currentColor"/></svg>'
            },
            {
                url: card.dataset.profileSocialBluesky,
                label: 'Bluesky',
                hosts: ['bsky.app', 'www.bsky.app'],
                svg: '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 11.2C10.9 9.1 7.9 5.3 5.1 3.6 2.4 2 .9 2.3.5 3.8c-.4 1.4.1 5.5.7 6.8 1 2.2 3.9 2.9 6.6 2.4-4.7.8-6 3.3-3.4 5.9 4.7 4.7 7-1.2 7.6-2.8.1-.3.2-.5.2-.4 0-.1.1.1.2.4.6 1.6 2.9 7.5 7.6 2.8 2.6-2.6 1.3-5.1-3.4-5.9 2.7.5 5.6-.2 6.6-2.4.6-1.3 1.1-5.4.7-6.8-.4-1.5-1.9-1.8-4.6-.2-2.8 1.7-5.8 5.5-6.9 7.6Z"/></svg>'
            }
        ];

        socialProfiles.forEach(function (profile) {
            if (!profile.url) return;

            let parsedUrl;
            try {
                parsedUrl = new URL(profile.url);
            } catch (error) {
                return;
            }

            if (parsedUrl.protocol !== 'https:' || !profile.hosts.includes(parsedUrl.hostname.toLowerCase())) {
                return;
            }

            const link = document.createElement('a');
            link.className = 'community-social-link';
            link.href = parsedUrl.href;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.setAttribute('aria-label', profile.label + ' profile (opens in a new tab)');
            link.title = profile.label;
            link.innerHTML = profile.svg;
            socialContainer.appendChild(link);
        });

        socialRow.hidden = socialContainer.children.length === 0;
        document.getElementById('profileKeyholderRow').hidden = !card.dataset.profileKeyholder;
        document.getElementById('profileContentRow').hidden = !card.dataset.profileContent;
        document.getElementById('profileDurationRow').hidden = !card.dataset.profileDuration;

        profileDialog.showModal();
    });

    document.getElementById('closeProfileDialog').addEventListener('click', function () {
        profileDialog.close();
    });

    [profileDialog].forEach(function (dialog) {
        dialog.addEventListener('click', function (event) {
            if (event.target === dialog) dialog.close();
        });
    });
})();