function openHistoryDialog(boxId) {
    const entries = boxHistoryData[boxId] || [];
    const container = document.getElementById('historyContent');
    document.getElementById('historyBoxName').textContent = 'LockMeBox ' + boxId;

    const summaryEl = document.getElementById('historySummary');

    if (entries.length === 0) {
        summaryEl.innerHTML = '';
        container.innerHTML = '<p class="history-empty">No history available yet.</p>';
        document.getElementById('historyDialog').showModal();
        return;
    }

    summaryEl.innerHTML = '';
    container.innerHTML = renderYearCalendar(entries);

    document.getElementById('historyDialog').showModal();
}

 
// Build continuous status intervals (open/closed) from the raw log entries.
// A "closed" interval is split at the point its timer expires (if any),
// since the box is effectively open from then on even before the next
// history row confirms it.
function buildStatusSegments(entries) {
    const sorted = [...entries].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    const segments = [];

    for (let i = 0; i < sorted.length; i++) {
        const entry = sorted[i];
        const start = new Date(entry.created_at.replace(' ', 'T'));
        const end = (i + 1 < sorted.length)
            ? new Date(sorted[i + 1].created_at.replace(' ', 'T'))
            : new Date(); // last known state extends until now

        if (end <= start) continue;

        const isClosed = entry.lock_status == 1;

        if (isClosed && entry.protection_level_timer && entry.open_time) {
            const timerExpiry = new Date(entry.open_time.replace(' ', 'T'));

            if (timerExpiry <= start) {
                // timer already expired at the moment this entry was created
                segments.push({ start, end, status: 'open' });
                continue;
            }

            if (timerExpiry < end) {
                // split: locked until the timer runs out, open afterwards
                segments.push({ start, end: timerExpiry, status: 'closed' });
                segments.push({ start: timerExpiry, end, status: 'open' });
                continue;
            }
            // timerExpiry >= end: timer hasn't run out within this window yet
        }

        segments.push({ start, end, status: isClosed ? 'closed' : 'open' });
    }

    return segments;
}

function dayKey(d) {
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

// ms per calendar day that were spent in "closed" (locked) state
function buildDayLockedMs(segments) {
    const dayMap = new Map();

    for (const seg of segments) {
        if (seg.status !== 'closed') continue;

        let cursor = new Date(seg.start);

        while (cursor < seg.end) {
            const dayStart = new Date(cursor.getFullYear(), cursor.getMonth(), cursor.getDate());
            const nextDayStart = new Date(dayStart.getFullYear(), dayStart.getMonth(), dayStart.getDate() + 1);
            const chunkEnd = seg.end < nextDayStart ? seg.end : nextDayStart;
            const key = dayKey(dayStart);

            dayMap.set(key, (dayMap.get(key) || 0) + (chunkEnd - cursor));
            cursor = chunkEnd;
        }
    }

    return dayMap;
}

function classifyDay(lockedMs) {
    const DAY_MS = 24 * 60 * 60 * 1000;
    if (!lockedMs || lockedMs <= 0) return 'open';
    if (lockedMs >= DAY_MS - 60000) return 'full'; // treat <1min slack as fully locked
    return 'partial';
}

function renderYearCalendar(entries) {
    const segments = buildStatusSegments(entries);
    const dayLockedMs = buildDayLockedMs(segments);

    const firstDay = new Date(segments[0].start.getFullYear(), segments[0].start.getMonth(), segments[0].start.getDate());
    const today = new Date();
    const lastDay = new Date(today.getFullYear(), today.getMonth(), today.getDate());

    // Group all days in range by year, then by month
    const years = new Map();
    let cursor = new Date(firstDay);

    while (cursor <= lastDay) {
        const year = cursor.getFullYear();
        const month = cursor.getMonth();
        const key = dayKey(cursor);
        const lockedMs = dayLockedMs.get(key) || 0;

        if (!years.has(year)) years.set(year, new Map());
        const monthsMap = years.get(year);
        if (!monthsMap.has(month)) monthsMap.set(month, []);

        monthsMap.get(month).push({
            date: new Date(cursor),
            state: classifyDay(lockedMs),
            pct: Math.round((lockedMs / (24 * 60 * 60 * 1000)) * 100)
        });

        cursor.setDate(cursor.getDate() + 1);
    }

    const yearKeys = Array.from(years.keys()).sort((a, b) => b - a);

    return yearKeys.map(function (year) {
        const monthsMap = years.get(year);
        const monthKeys = Array.from(monthsMap.keys()).sort((a, b) => b - a);

        const monthsHtml = monthKeys.map(function (month) {
            const days = monthsMap.get(month);
            const monthLabel = days[0].date.toLocaleDateString('de-DE', { month: 'short' });

            // Monday-first offset for the 1st of the month
            const firstWeekday = (days[0].date.getDay() + 6) % 7;
            const emptyCells = Array.from({ length: firstWeekday }, () =>
                '<span class="ycal-day ycal-empty"></span>'
            ).join('');

            const dayCells = days.map(function (d) {
                const title = d.date.toLocaleDateString('de-DE') + ' – ' + d.pct + '% locked';
                return '<span class="ycal-day ycal-' + d.state + '" title="' + title + '"></span>';
            }).join('');

            return '<div class="ycal-month">' +
                '<div class="ycal-month-label">' + monthLabel + '</div>' +
                '<div class="ycal-grid">' + emptyCells + dayCells + '</div>' +
                '</div>';
        }).join('');

        return '<div class="ycal-year">' +
            '<div class="ycal-year-label">' + year + '</div>' +
            '<div class="ycal-months">' + monthsHtml + '</div>' +
            '</div>';
    }).join('');
}