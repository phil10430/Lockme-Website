 /*--------------- after code change in js -> cmd+shift+R --------------*/
function openOpenDialog() {
    document.getElementById("open-dialog").style.display = "flex";
}

function closeOpenDialog() {
    document.getElementById("open-dialog").style.display = "none";
    document.getElementById("open-error").style.display = "none";
    document.getElementById("open-password").value = "";
}

function submitOpenDialog() {
    var pw  = document.getElementById("open-password").value;
    var err = document.getElementById("open-error");

    if (pw.length < 1) {
        err.style.display = "block";
        err.textContent = "Please enter a password.";
        return;
    }

    document.getElementById("password").value = pw;  // verstecktes Feld
    document.getElementById("openBoxWithPw").click(); // versteckter Submit
    closeOpenDialog();
}