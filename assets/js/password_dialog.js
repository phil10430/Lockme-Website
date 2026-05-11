function openPasswordDialog() {
    var dialog = document.getElementById("password-dialog");
    dialog.style.display = "flex";
}

function closePasswordDialog() {
    document.getElementById("password-dialog").style.display = "none";
    document.getElementById("dialog-error").style.display = "none";
    document.getElementById("dialog-password").value = "";
    document.getElementById("dialog-confirm-password").value = "";
    document.getElementById("dialog-confirm-checkbox").checked = false;
}

function submitPasswordDialog() {
    var pw  = document.getElementById("dialog-password").value;
    var pw2 = document.getElementById("dialog-confirm-password").value;
    var cb  = document.getElementById("dialog-confirm-checkbox").checked;
    var err = document.getElementById("dialog-error");

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
    if (!cb) {
        err.style.display = "block";
        err.textContent = "Please confirm you understand.";
        return;
    }

    // Passwort abschicken — hier dein Form-Submit oder AJAX
    document.getElementById("password").value = pw;
    document.getElementById("closeBoxWithPw").click();  // klickt den versteckten Submit
    closePasswordDialog();
}