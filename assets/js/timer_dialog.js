function openTimerDialog() {
    var dialog = document.getElementById("timer-dialog");
    dialog.style.display = "flex";
    
     // heutiges Datum setzen
    var today = new Date().toISOString().split("T")[0];
    document.getElementById("timer-date").value = today;

    var now = new Date();
    document.getElementById("timer-time").value =
    now.toTimeString().slice(0,5);
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
    // date kommt als "2026-05-11", umformatieren zu "11/05/2026"
    var parts = date.split("-");
    var formattedDate = parts[2] + "/" + parts[1] + "/" + parts[0];
    
    // Wert ins versteckte Feld setzen und absenden
    document.getElementById("openTimeField").value = formattedDate + " " + time;
    document.getElementById("closeBoxWithTimer").click();  // versteckter Submit
    closeTimerDialog();
}