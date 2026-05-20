function openRandomTimerDialog() {
    document.getElementById('rt-min-days').value    = 0;
    document.getElementById('rt-min-hours').value   = 0;
    document.getElementById('rt-min-minutes').value = 0;
    document.getElementById('rt-max-days').value    = 0;
    document.getElementById('rt-max-hours').value   = 0;
    document.getElementById('rt-max-minutes').value = 0;
    document.getElementById('rt-checkbox').checked  = false;
    document.getElementById('rt-error').style.display = 'none';
    document.getElementById('random-timer-dialog').style.display = 'flex';
}

function closeRandomTimerDialog() {
    document.getElementById('random-timer-dialog').style.display = 'none';
}

function submitRandomTimerDialog() {
    var err = document.getElementById('rt-error');
    err.style.display = 'none';

    var minTotal = parseInt(document.getElementById('rt-min-days').value)    * 24 * 60
                 + parseInt(document.getElementById('rt-min-hours').value)   * 60
                 + parseInt(document.getElementById('rt-min-minutes').value);

    var maxTotal = parseInt(document.getElementById('rt-max-days').value)    * 24 * 60
                 + parseInt(document.getElementById('rt-max-hours').value)   * 60
                 + parseInt(document.getElementById('rt-max-minutes').value);

    if (maxTotal === 0) {
        err.style.display = 'block';
        err.textContent = 'Please select a maximum time.';
        return;
    }
    if (minTotal >= maxTotal) {
        err.style.display = 'block';
        err.textContent = 'Maximum time must be greater than minimum time.';
        return;
    }
    if (!document.getElementById('rt-checkbox').checked) {
        err.style.display = 'block';
        err.textContent = 'Please confirm you understand.';
        return;
    }

    // Zufällige Zeit in Minuten
    var randomMinutes = minTotal + Math.floor(Math.random() * (maxTotal - minTotal));

    // Unix Timestamp
    var unixTime = Math.floor(Date.now() / 1000) + randomMinutes * 60;
    $("#openTimeRandomField").val(unixTime);
    $("#closeBoxWithRandomTimer").click(); // oder dein bestehender submit

    closeRandomTimerDialog();
}