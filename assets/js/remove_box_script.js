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

    if (lockStart) {
        totalMs += (new Date() - lockStart);
    }

    const totalHours = totalMs / (1000 * 60 * 60);
    const days = Math.floor(totalHours / 24);
    const hours = Math.floor(totalHours % 24);

    return {
        days,
        hours,
        isCurrentlyLocked: lockStart !== null,
        lockingEntry // null if not currently locked
    };
}

function formatOpenTime(openTime) {
    if (!openTime) return '';
    const d = new Date(openTime.replace(' ', 'T'));
    return d.toLocaleDateString('de-DE') + ' ' + d.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
}

function buildCurrentStatusHtml(duration) {
    if (!duration.isCurrentlyLocked) return '';

    const entry = duration.lockingEntry;

    let liveText = 'Currently locked';

    if (entry) {
        if (entry.protection_level_timer == 1 && entry.open_time) {
            liveText += ' until ' + formatOpenTime(entry.open_time);
        } else if (entry.protection_level_password == 1) {
            liveText += ' by password 🔑';
        }
    }

    return '<span class="history-summary-live">' + liveText + '</span>';
}

function openHistoryDialog(boxId) {
    const entries = boxHistoryData[boxId] || [];
    const container = document.getElementById('historyContent');
    document.getElementById('historyBoxName').textContent = 'LockMeBox ' + boxId;

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
            '<span class="history-summary-value">' + duration.days + 'd ' +  '</span>' +
            buildCurrentStatusHtml(duration) +
        '</div>';

    container.innerHTML = renderMonthlyBars(entries);

    document.getElementById('historyDialog').showModal();
}



// Build continuous status intervals (open/closed) from the raw log entries
function buildStatusSegments(entries) {
    const sorted = [...entries].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    const segments = [];

    for (let i = 0; i < sorted.length; i++) {
        const start = new Date(sorted[i].created_at.replace(' ', 'T'));
        const end = (i + 1 < sorted.length)
            ? new Date(sorted[i + 1].created_at.replace(' ', 'T'))
            : new Date(); // last known state extends until now

        if (end > start) {
            segments.push({
                start,
                end,
                status: sorted[i].lock_status == 1 ? 'closed' : 'open'
            });
        }
    }

    return segments;
}

// Split segments so none of them cross a calendar-month boundary
function splitSegmentsByMonth(segments) {
    const result = [];

    for (const seg of segments) {
        let cursor = new Date(seg.start);

        while (cursor < seg.end) {
            const monthStart = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
            const nextMonthStart = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 1);
            const chunkEnd = seg.end < nextMonthStart ? seg.end : nextMonthStart;
            const monthKey = cursor.getFullYear() + '-' + String(cursor.getMonth() + 1).padStart(2, '0');

            result.push({
                monthKey,
                monthStart,
                nextMonthStart,
                start: new Date(cursor),
                end: new Date(chunkEnd),
                status: seg.status
            });

            cursor = chunkEnd;
        }
    }

    return result;
}

// Group the month-clipped segments into a Map keyed by "YYYY-MM"
function groupSegmentsByMonth(splitSegments) {
    const map = new Map();

    for (const s of splitSegments) {
        if (!map.has(s.monthKey)) {
            map.set(s.monthKey, {
                monthStart: s.monthStart,
                nextMonthStart: s.nextMonthStart,
                segments: []
            });
        }
        map.get(s.monthKey).segments.push(s);
    }

    return map;
}

function renderMonthlyBars(entries) {
    const segments = buildStatusSegments(entries);
    const splitSegments = splitSegmentsByMonth(segments);
    const monthMap = groupSegmentsByMonth(splitSegments);

    const monthKeys = Array.from(monthMap.keys()).sort().reverse();

    return monthKeys.map(function (monthKey) {
        const monthData = monthMap.get(monthKey);
        const monthLabel = monthData.monthStart.toLocaleDateString('de-DE', { month: 'long', year: 'numeric' });
        const monthDurationMs = monthData.nextMonthStart - monthData.monthStart;

        let lockedMsInMonth = 0;

        const segmentsHtml = monthData.segments.map(function (seg) {
            const leftPct = ((seg.start - monthData.monthStart) / monthDurationMs) * 100;
            const widthPct = ((seg.end - seg.start) / monthDurationMs) * 100;
            const statusClass = seg.status === 'closed' ? 'segment-closed' : 'segment-open';

            if (seg.status === 'closed') {
                lockedMsInMonth += (seg.end - seg.start);
            }

            const title = seg.start.toLocaleDateString('de-DE') + ' ' +
                seg.start.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' }) +
                ' – ' +
                seg.end.toLocaleDateString('de-DE') + ' ' +
                seg.end.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });

            return '<div class="history-segment ' + statusClass + '" ' +
                'style="left:' + leftPct.toFixed(3) + '%;width:' + Math.max(widthPct, 0.2).toFixed(3) + '%" ' +
                'title="' + title + '"></div>';
        }).join('');

      const lockedDays = Math.round(lockedMsInMonth / (1000 * 60 * 60 * 24));

return '<div class="history-month-row">' +
    '<span class="history-month-label">' + monthLabel + '</span>' +
    '<div class="history-month-bar">' + segmentsHtml + '</div>' +
    '<div class="history-days-column">' + lockedDays + 'd locked</div>' +
'</div>';
    }).join('');
}

function getCurrentStatus(entries) {
    if (!entries || entries.length === 0) {
        return { text: 'No data', className: 'box-status-none' };
    }

    const duration = calculateLockedDuration(entries);

    if (!duration.isCurrentlyLocked) {
        return { text: '🔓 Open', className: 'box-status-open' };
    }

    const entry = duration.lockingEntry;
    let text = '🔒 Locked';

    if (entry) {
        if (entry.protection_level_timer == 1 && entry.open_time) {
            text += ' until ' + formatOpenTime(entry.open_time);
        } else if (entry.protection_level_password == 1) {
            text += ' by password ';
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