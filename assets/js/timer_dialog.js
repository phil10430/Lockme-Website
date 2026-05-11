function openTimerDialog() {
    var dialog = document.getElementById("timer-dialog");
    dialog.style.display = "flex";
}

function closeTimerDialog() {
    document.getElementById("timer-dialog").style.display = "none";
    document.getElementById("timer-error").style.display = "none";
    document.getElementById("timer-date").value = "";
    document.getElementById("timer-time").value = "";
    document.getElementById("timer-confirm-checkbox").checked = false;
}

function submitTimerDialog() {
    var date = document.getElementById("timer-date").value;
    var time = document.getElementById("timer-time").value;
    var cb   = document.getElementById("timer-confirm-checkbox").checked;
    var err  = document.getElementById("timer-error");

    if (!date) {
        err.style.display = "block";
        err.textContent = "Please select a date.";
        return;
    }
    if (!time) {
        err.style.display = "block";
        err.textContent = "Please select a time.";
        return;
    }
    if (new Date(date + "T" + time) <= new Date()) {
        err.style.display = "block";
        err.textContent = "Date and time must be in the future.";
        return;
    }
    if (!cb) {
        err.style.display = "block";
        err.textContent = "Please confirm you understand.";
        return;
    }

    // Wert ins versteckte Feld setzen und absenden
    document.getElementById("openTimeField").value = date + " " + time;
    document.getElementById("setTimer").click();  // versteckter Submit
    closeTimerDialog();
}