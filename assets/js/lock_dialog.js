 /*--------------- after code change in js -> cmd+shift+R --------------*/
function lockDialog(mode) {

   // Reset: alle Felder auf definierten Ausgangszustand

    $("#lock-dialog-password-group").hide();
    $("#lock-dialog-confirm-group").hide();
    $("#lock-dialog-date-group").hide();
    $("#lock-dialog-time-group").hide();

    var showPassword        =   (mode === 'password' || mode === 'passwordTimer') ||
                                (mode === 'extendTime' && window.protectionLevelPassword == 1);
    var showConfirmPassword =   (mode === 'password' || mode === 'passwordTimer');
    var showTimer           =   (mode === 'timer' || mode === 'passwordTimer' || mode === 'extendTime');
    
    // Felder ein/ausblenden
    if (showPassword)        $("#lock-dialog-password-group").show();
    if (showConfirmPassword) $("#lock-dialog-confirm-group").show();
    if (showTimer)           $("#lock-dialog-date-group").show();
    if (showTimer)           $("#lock-dialog-time-group").show();

    // Titel anpassen
    var titles = {
        'password':      'Lock by Password',
        'timer':         'Lock by Timer',
        'passwordTimer': 'Lock by Password & Timer',
        'extendTime':   'Extend Timer'
    };

     // Titel anpassen
    var btnSubmitName = {
        'password':      'Lock',
        'timer':         'Lock',
        'passwordTimer': 'Lock',
        'extendTime':   'Extend Timer'
    };

    $("#lock-dialog-title").text(titles[mode]);
    $("#lock-dialog-btn-submit").text(btnSubmitName[mode]);

    var checkboxLabels = {
    'password':      'I understand that losing the password will permanently prevent access to the box.',
    'timer':         'I confirm that I entered the correct date and time. There is no unlock option before that.',
    'passwordTimer': 'I confirm that I entered the correct password, date and time. There is no unlock option without password before that.',
    'extendTime':    'I confirm that I entered the correct date and time. There is no unlock option without password before that.'
    };
    $("#lock-dialog-checkbox-label").text(checkboxLabels[mode]);


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
function togglePassword(inputId, icon) {
    var input = document.getElementById(inputId);
    var isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";
    $(icon).find("i").toggleClass("ti-eye ti-eye-off");
}

function submitLockDialog() {
    var mode = $("#lock-dialog").data("mode");
    var showPassword        = (mode === 'password' || mode === 'passwordTimer') ||
                            (mode === 'extendTime' && window.protectionLevelPassword == 1);
    var showConfirmPassword = (mode === 'password' || mode === 'passwordTimer');
    var showTimer           = (mode === 'timer' || mode === 'passwordTimer' || mode === 'extendTime');
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