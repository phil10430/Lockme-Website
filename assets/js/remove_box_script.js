let boxIdToRemove = null;

function removeBox(boxId) {
    console.log('removeBox called with:', boxId); // TEST
    boxIdToRemove = boxId;
    document.getElementById('removeBoxName').textContent = boxId;
    document.getElementById('removeBoxDialog').showModal();
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmRemoveBtn').addEventListener('click', function() {
        console.log('Confirm clicked, boxIdToRemove is:', boxIdToRemove); // TEST
        const boxId = boxIdToRemove;
        document.getElementById('removeBoxDialog').close();

        fetch('/profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'removeBox=1&boxId=' + encodeURIComponent(boxId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('box-' + boxId).remove();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    });
});



function calculateLockedDuration(entries) {
    const sorted = [...entries].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

    let totalMs = 0;
    let lockStart = null;
    let lockingEntry = null; // the entry that started the current lock, if any

    for (const entry of sorted) {
        if (entry.lock_status == 1) {
            lockStart = new Date(entry.created_at.replace(' ', 'T'));
            lockingEntry = entry;
        } else if (entry.lock_status == 0 && lockStart) {
            const openedAt = new Date(entry.created_at.replace(' ', 'T'));
            totalMs += (openedAt - lockStart);
            lockStart = null;
            lockingEntry = null;
        }
    }

    let isCurrentlyLocked = lockStart !== null;

    if (lockStart) {
        let effectiveEnd = new Date();

        // A timer-protected box counts as open again once its timer
        // has expired, even if no new history row confirms it yet.
        if (lockingEntry && lockingEntry.protection_level_timer == 1 && lockingEntry.open_time) {
            const timerExpiry = new Date(lockingEntry.open_time.replace(' ', 'T'));
            if (timerExpiry <= new Date()) {
                effectiveEnd = timerExpiry;
                isCurrentlyLocked = false;
            }
        }

        totalMs += (effectiveEnd - lockStart);
    }

    const totalHours = totalMs / (1000 * 60 * 60);
    const days = Math.floor(totalHours / 24);
    const hours = Math.floor(totalHours % 24);

    return {
        days,
        hours,
        isCurrentlyLocked,
        lockingEntry: isCurrentlyLocked ? lockingEntry : null
    };
}

function formatOpenTime(openTime) {
    if (!openTime) return '';
    const d = new Date(openTime.replace(' ', 'T'));
    return d.toLocaleDateString('de-DE') + ' ' + d.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
}


function getCurrentStatus(entries) {
    if (!entries || entries.length === 0) {
        return { text: 'No data', className: 'box-status-none' };
    }

    const duration = calculateLockedDuration(entries);

    if (!duration.isCurrentlyLocked) {
        return { text: 'Open', className: 'box-status-open' };
    }

    const entry = duration.lockingEntry;
    let text = '';

    if (entry) {
        if (entry.protection_level_timer == 1 && entry.open_time) {
            text = ' until ' + formatOpenTime(entry.open_time);
        } else if (entry.protection_level_password == 1) {
            text = ' by password ';
        }
    }

    return { text, className: 'box-status-locked' };
}

function renderBoxStatuses() {
    Object.keys(boxHistoryData).forEach(function (boxId) {
        const el = document.getElementById('box-status-' + boxId);
        if (!el) return;

        const status = getCurrentStatus(boxHistoryData[boxId]);
        el.textContent = status.text;
        el.className = 'box-status ' + status.className;
    });
}

document.addEventListener('DOMContentLoaded', renderBoxStatuses);