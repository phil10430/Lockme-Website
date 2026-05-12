function lockDialog(mode) {
    // mode: 'password', 'timer', 'passwordTimer'
    
    var showPassword        = (mode === 'password' || mode === 'passwordTimer'|| mode === 'extendTime');
    var showConfirmPassword = (mode === 'password' || mode === 'passwordTimer');
    var showTimer           = (mode === 'timer'    || mode === 'passwordTimer'|| mode === 'extendTime');
    
    // Felder ein/ausblenden
    $("#lock-dialog-password-group").toggle(showPassword);
    $("#lock-dialog-confirm-group").toggle(showConfirmPassword);
    $("#lock-dialog-date-group").toggle(showTimer);
    $("#lock-dialog-time-group").toggle(showTimer);

    // Titel anpassen
    var titles = {
        'password':      'Lock by Password',
        'timer':         'Lock by Timer',
        'passwordTimer': 'Lock by Password & Timer',
        'extendTime':   'Extend Timer'
    };
    $("#lock-dialog-title").text(titles[mode]);

    // Mode speichern für Submit
    $("#lock-dialog").data("mode", mode);

    // Datum/Zeit vorausfüllen
    if (showTimer) {
        var today = new Date().toISOString().split("T")[0];
        $("#lock-dialog-date").val(today);
        $("#lock-dialog-time").val(new Date().toTimeString().slice(0, 5));
    }

    $("#lock-dialog").css("display", "flex");
}

function closeLockDialog() {
    $("#lock-dialog").hide();
    $("#lock-dialog-error").hide();
    $("#lock-dialog-password").val("");
    $("#lock-dialog-confirm").val("");
    $("#lock-dialog-date").val("");
    $("#lock-dialog-time").val("");
    $("#lock-dialog-checkbox").prop("checked", false);
}

function submitLockDialog() {
    var mode = $("#lock-dialog").data("mode");
    var showPassword        = (mode === 'password' || mode === 'passwordTimer'|| mode === 'extendTime');
    var showConfirmPassword = (mode === 'password' || mode === 'passwordTimer');
    var showTimer           = (mode === 'timer'    || mode === 'passwordTimer' || mode === 'extendTime');
    var err = $("#lock-dialog-error");

    var pw  = $("#lock-dialog-password").val();
    var pw2 = $("#lock-dialog-confirm").val();

    if (showPassword) {
        if (pw.length < 1) { err.show().text("Please enter a password."); return; }
    }
    if (showConfirmPassword) {
        if (pw !== pw2) { err.show().text("Passwords do not match."); return; }
    }
    if (showTimer) {
        var date = $("#lock-dialog-date").val();
        var time = $("#lock-dialog-time").val();
        if (!date) { err.show().text("Please select a date."); return; }
        if (!time) { err.show().text("Please select a time."); return; }
        if (new Date(date + "T" + time) <= new Date()) {
            err.show().text("Date and time must be in the future."); return;
        }
    }
    if (!$("#lock-dialog-checkbox").prop("checked")) {
        err.show().text("Please confirm you understand."); return;
    }

    if (showPassword) {
        $("#password").val(pw);
    }
    if (showTimer) {
        var parts = $("#lock-dialog-date").val().split("-");
        var formattedDate = parts[2] + "/" + parts[1] + "/" + parts[0];
        $("#openTimeField").val(formattedDate + " " + $("#lock-dialog-time").val());
    }

    var submitButtons = {
        'password':      '#closeBoxWithPw',
        'timer':         '#closeBoxWithTimer',
        'passwordTimer': '#closeBoxWithPasswordTimer',
        'extendTime':   '#extendTime'
    };

    $(submitButtons[mode]).click();
    closeLockDialog();
}