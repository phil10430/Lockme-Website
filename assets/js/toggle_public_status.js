function togglePublicStatus(isPublic) {
    const toggle = document.getElementById('publicStatusToggle');
    toggle.disabled = true;

    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `togglePublicStatus=1&public_status=${isPublic ? '1' : '0'}`
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            toggle.checked = !isPublic;
            alert(data.message || 'Could not update status.');
        }
    })
    .catch(() => {
        toggle.checked = !isPublic;
        alert('Network error.');
    })
    .finally(() => {
        toggle.disabled = false;
    });
}