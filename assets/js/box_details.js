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