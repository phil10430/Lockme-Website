let currentDetailsBoxId = null;

function openBoxDetailsDialog(boxId) {
    currentDetailsBoxId = boxId;
    const d = (window.boxDetails && window.boxDetails[boxId]) || {};

    document.getElementById('detailsBoxName').textContent = boxId;
    document.getElementById('detailsNameTop').value = d.name_top || '';
    document.getElementById('detailsNameSub').value = d.name_sub || '';
    document.getElementById('detailsBoxContent').value = d.box_content || '';
    document.getElementById('detailsTargetDate').value =
        d.target_open_date ? d.target_open_date.replace(' ', 'T').slice(0, 16) : '';

    document.getElementById('detailsError').style.display = 'none';
    document.getElementById('boxDetailsDialog').showModal();
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('boxDetailsForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const errorBox = document.getElementById('detailsError');
        errorBox.style.display = 'none';

        const params = new URLSearchParams();
        params.set('saveBoxDetails', '1');
        params.set('boxId', currentDetailsBoxId);
        params.set('name_top', document.getElementById('detailsNameTop').value);
        params.set('name_sub', document.getElementById('detailsNameSub').value);
        params.set('box_content', document.getElementById('detailsBoxContent').value);
        params.set('target_open_date', document.getElementById('detailsTargetDate').value);

        fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                errorBox.textContent = data.message || 'Could not save details.';
                errorBox.style.display = 'block';
            }
        })
        .catch(() => {
            errorBox.textContent = 'Network error.';
            errorBox.style.display = 'block';
        });
    });
});


function openShareDialog(boxId) {
    const url = window.location.origin + '/box_share.php?box_id=' + encodeURIComponent(boxId);
    const text = 'My LockMeBox status:';

    document.getElementById('shareBoxName').textContent = boxId;
    document.getElementById('shareLinkInput').value = url;
    document.getElementById('shareCopiedMsg').style.display = 'none';

    document.getElementById('shareTwitterLink').href =
        'https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(text);

    document.getElementById('shareBlueskyLink').href =
        'https://bsky.app/intent/compose?text=' + encodeURIComponent(text + ' ' + url);

    document.getElementById('shareDialog').showModal();
}

function copyShareLink() {
    const input = document.getElementById('shareLinkInput');
    input.select();

    navigator.clipboard.writeText(input.value).then(() => {
        document.getElementById('shareCopiedMsg').style.display = 'block';
    }).catch(() => {
        document.execCommand('copy');
        document.getElementById('shareCopiedMsg').style.display = 'block';
    });
}