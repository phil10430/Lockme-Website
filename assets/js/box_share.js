window.openShareDialog = function (boxId) {
    const id  = String(boxId);
    const url = location.origin + '/community.php?box=' + encodeURIComponent(id);
    const text = 'My LockMeBox status';

    document.getElementById('shareBoxName').textContent = id;
    document.getElementById('shareLinkInput').value = url;
    document.getElementById('shareCopiedMsg').style.display = 'none';

    // Hinweis, wenn das Profil privat ist (Link zeigt dann nichts an)
    document.getElementById('sharePublicWarning').style.display =
        window.profileVisibility === 'public' ? 'none' : 'block';

    document.getElementById('shareTwitterLink').href =
        'https://twitter.com/intent/tweet?text=' + encodeURIComponent(text) + '&url=' + encodeURIComponent(url);
    document.getElementById('shareBlueskyLink').href =
        'https://bsky.app/intent/compose?text=' + encodeURIComponent(text + ' ' + url);

    document.getElementById('shareDialog').showModal();
};

window.copyShareLink = function () {
    const input = document.getElementById('shareLinkInput');
    const done = function () {
        const msg = document.getElementById('shareCopiedMsg');
        msg.style.display = 'block';
        setTimeout(function () { msg.style.display = 'none'; }, 2000);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(input.value).then(done);
    } else {
        input.select();
        document.execCommand('copy');
        done();
    }
};