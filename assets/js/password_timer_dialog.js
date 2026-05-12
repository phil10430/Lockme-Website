function openPasswordTimerDialog() {
    var dialog = document.getElementById("password-timer-dialog");
    dialog.style.display = "flex";
    var today = new Date().toISOString().split("T")[0];
    document.getElementById("pt-date").value = today;
    var now = new Date();
    document.getElementById("pt-time").value = now.toTimeString().slice(0, 5);
}

function closePasswordTimerDialog() {
    document.getElementById("password-timer-dialog").style.display = "none";
    document.getElementById("pt-error").style.display = "none";
    document.getElementById("pt-password").value = "";
    document.getElementById("pt-confirm-password").value = "";
    document.getElementById("pt-date").value = "";
    document.getElementById("pt-time").value = "";
    document.getElementById("pt-confirm-checkbox").checked = false;
}

function submitPasswordTimerDialog() {
    var pw   = document.getElementById("pt-password").value;
    var pw2  = document.getElementById("pt-confirm-password").value;
    var date = document.getElementById("pt-date").value;
    var time = document.getElementById("pt-time").value;
    var cb   = document.getElementById("pt-confirm-checkbox").checked;
    var err  = document.getElementById("pt-error");

    if (pw.length < 1) {
        err.style.display = "block";
        err.textContent = "Please enter a password.";
        return;
    }
    if (pw !== pw2) {
        err.style.display = "block";
        err.textContent = "Passwords do not match.";
        return;
    }
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

    var parts = date.split("-");
    var formattedDate = parts[2] + "/" + parts[1] + "/" + parts[0];

    document.getElementById("password").value = pw;
    document.getElementById("openTimeField").value = formattedDate + " " + time;
    document.getElementById("closeBoxWithPasswordTimer").click();
    closePasswordTimerDialog();
}