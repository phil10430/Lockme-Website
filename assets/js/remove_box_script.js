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


function openHistoryDialog(boxId) {
    const entries = boxHistoryData[boxId] || [];
    const container = document.getElementById('historyContent');
    document.getElementById('historyBoxName').textContent = 'LockMeBox ' + boxId;

    if (entries.length === 0) {
        container.innerHTML = '<p class="history-empty">No history available yet.</p>';
    } else {
        container.innerHTML = entries.map(function(entry) {
            const isClosed = entry.lock_status == 1;
            const statusClass = isClosed ? 'status-closed' : 'status-open';
            const statusIcon = isClosed ? '🔒' : '🔓';
            const statusText = isClosed ? 'Closed' : 'Opened';

            const date = new Date(entry.created_at.replace(' ', 'T'));
            const formattedDate = date.toLocaleDateString('de-DE') + ' ' + date.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });

            let openTimeText = '';
            if (entry.open_time) {
                const openDate = new Date(entry.open_time.replace(' ', 'T'));
                openTimeText = '<span class="history-time">Opened at: ' + openDate.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' }) + '</span>';
            }

            const badges = [];
            if (entry.protection_level_timer == 1) {
                badges.push('<span class="history-badge active">Timer</span>');
            }
            if (entry.protection_level_password == 1) {
                badges.push('<span class="history-badge active">Password</span>');
            }

            return '<div class="history-entry ' + statusClass + '">' +
                       '<span class="history-icon">' + statusIcon + '</span>' +
                       '<div class="history-details">' +
                           '<span class="history-status">' + statusText + '</span>' +
                           '<span class="history-time">' + formattedDate + '</span>' +
                           openTimeText +
                           (badges.length > 0 ? '<div class="history-badges">' + badges.join('') + '</div>' : '') +
                       '</div>' +
                   '</div>';
        }).join('');
    }

    document.getElementById('historyDialog').showModal();
}