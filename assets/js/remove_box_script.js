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

    // Total locked duration berechnen
    const duration = calculateLockedDuration(entries);
    const summaryEl = document.getElementById('historySummary');

    if (entries.length === 0) {
        summaryEl.innerHTML = '';
        container.innerHTML = '<p class="history-empty">No history available yet.</p>';
        document.getElementById('historyDialog').showModal();
        return;
    }

    summaryEl.innerHTML =
        '<div class="history-summary-box">' +
            '<span class="history-summary-label">Total time locked</span>' +
            '<span class="history-summary-value">' + duration.days + 'd ' + duration.hours + 'h</span>' +
            (duration.isCurrentlyLocked ? '<span class="history-summary-live">Currently locked</span>' : '') +
        '</div>';

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

    document.getElementById('historyDialog').showModal();
}

function calculateLockedDuration(entries) {
    // Chronologisch aufsteigend sortieren (entries kommen DESC aus PHP)
    const sorted = [...entries].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

    let totalMs = 0;
    let lockStart = null;

    for (const entry of sorted) {
        if (entry.lock_status == 1) {
            // Box wurde geschlossen -> Startzeit merken
            lockStart = new Date(entry.created_at.replace(' ', 'T'));
        } else if (entry.lock_status == 0 && lockStart) {
            // Box wurde geöffnet -> Dauer seit lockStart addieren
            const openedAt = new Date(entry.created_at.replace(' ', 'T'));
            totalMs += (openedAt - lockStart);
            lockStart = null;
        }
    }

    // Falls die Box aktuell noch verschlossen ist (letzter Eintrag = closed, kein passendes open danach)
    if (lockStart) {
        totalMs += (new Date() - lockStart);
    }

    const totalHours = totalMs / (1000 * 60 * 60);
    const days = Math.floor(totalHours / 24);
    const hours = Math.floor(totalHours % 24);

    return { days, hours, isCurrentlyLocked: lockStart !== null };
}


function buildDayBars(entries) {
    if (entries.length === 0) return [];

    const events = [...entries]
        .sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
        .map(e => ({
            time: new Date(e.created_at.replace(' ', 'T')),
            locked: e.lock_status == 1,
            timer: e.protection_level_timer == 1,
            password: e.protection_level_password == 1
        }));

    const now = new Date();
    const firstDay = new Date(events[0].time.getFullYear(), events[0].time.getMonth(), events[0].time.getDate());
    const lastDay = new Date(now.getFullYear(), now.getMonth(), now.getDate());

    // Zustandssegmente zwischen den Events bilden
    const segments = [];
    for (let i = 0; i < events.length; i++) {
        const start = events[i].time;
        const end = i + 1 < events.length ? events[i + 1].time : now;
        segments.push({
            start,
            end,
            state: events[i].locked ? 'locked' : 'open',
            timer: events[i].timer,
            password: events[i].password
        });
    }

    // Segmente pro Kalendertag aufteilen
    const days = [];
    for (let d = new Date(firstDay); d <= lastDay; d.setDate(d.getDate() + 1)) {
        const dayStart = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0, 0, 0);
        const dayEndExclusive = new Date(d.getFullYear(), d.getMonth(), d.getDate() + 1, 0, 0, 0);

        const daySegments = [];
        let hasProtectionTimer = false;
        let hasProtectionPassword = false;

        for (const seg of segments) {
            const segStart = seg.start < dayStart ? dayStart : seg.start;
            const segEnd = seg.end > dayEndExclusive ? dayEndExclusive : seg.end;

            if (segStart < segEnd) {
                const startHour = (segStart - dayStart) / (1000 * 60 * 60);
                const endHour = (segEnd - dayStart) / (1000 * 60 * 60);
                daySegments.push({ startHour, endHour, state: seg.state });

                if (seg.state === 'locked') {
                    if (seg.timer) hasProtectionTimer = true;
                    if (seg.password) hasProtectionPassword = true;
                }
            }
        }

        if (daySegments.length > 0) {
            days.push({
                date: new Date(d),
                segments: daySegments,
                hasProtectionTimer,
                hasProtectionPassword
            });
        }
    }

    return days.reverse(); // neuester Tag zuerst
}
