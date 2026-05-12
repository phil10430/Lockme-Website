function refreshData(name){
    $(document).ready(function(){
        function getData(){
            $.ajax({
                type: 'POST',
                data: {username: name},
                url: 'autorefresh.php',
                success: function(data){
                    var json = JSON.parse(data);
                    window.protectionLevelPassword = json.protectionLevelPassword;
                    window.protectionLevelTimer    = json.protectionLevelTimer;
                   if (json.appLoggedIn==1 && json.boxName!=0 && json.appActive==1) {
                        $("#box-control-form").show();

                        if (json.lockStatus == 0) {
                            // Box offen
                            
                            $("#bg-image").attr("src", "/assets/images/icon_box_open.png");

                            // Anzeigen
                            $("#close-box-pw-btn").show();
                            $("#close-box-timer").show();
                            $("#close-box-pwtimer").show();
                            $("#label-choose-lock").show();

                            // Verstecken
                            $("#protection-level-wrapper").hide();
                            $("#locked-text").hide();
                            $("#locked-since").hide();
                            $("#openBox").hide();
                            $("#open-box-pw-btn").hide();
                            $("#extend-time-btn").hide();
                            $("#password-symbol").hide();
                            $("#time-left").hide();
                            $("#open-time").hide();

                        } else {
                            // Box geschlossen
                            $("#bg-image").attr("src", "/assets/images/icon_box_closed.png");

                            // Anzeigen
                            $("#locked-text").show().html("LOCKED");
                            $("#locked-since").show().html(json.lockedSince + "<br>");

                            // Verstecken
                            $("#close-box-pw-btn").hide();
                            $("#close-box-timer").hide();
                            $("#close-box-pwtimer").hide();
                            $("#label-choose-lock").hide();
                      
                            // Protection Level
                            if ((json.protectionLevelPassword == 1)  && (json.protectionLevelTimer == 1)) {
                                $("#protection-level-wrapper").show();
                                $("#password-symbol").show();
                                $("#timer-symbol").show();
                                $("#open-box-pw-btn").show();
                                $("#time-left").show().text(json.timeLeft);
                                $("#open-time").show().text(json.openTime);
                                $("#extend-time-btn").show();
                            }else if (json.protectionLevelPassword == 1) {         
                                $("#protection-level-wrapper").show(); 
                                $("#password-symbol").show();
                                $("#open-box-pw-btn").show();
                                $("#timer-symbol").hide();
                                $("#openBox").hide();
                                $("#time-left").hide();
                                $("#open-time").hide();
                                $("#extend-time-btn").hide();
                            }else if (json.protectionLevelTimer == 1) {
                                $("#protection-level-wrapper").show();
                                $("#password-symbol").hide();
                                $("#timer-symbol").show();
                                $("#open-box-pw-btn").hide();
                                $("#openBox").show();
                                $("#time-left").show().text(json.timeLeft);
                                $("#open-time").show().text(json.openTime);
                                $("#extend-time-btn").show();
                            }else {
                                $("#protection-level-wrapper").hide();
                                $("#password-symbol").hide();
                                $("#timer-symbol").hide();
                                $("#open-box-pw-btn").hide();
                                $("#openBox").show();
                                $("#time-left").hide();
                                $("#open-time").hide();
                                $("#extend-time-btn").hide();
                            }
                        }

                    } else {
                        // Nicht verbunden
                        $("#bg-image").attr("src", "/assets/images/lmb_start.png");
                        $("#box-control-form").hide();
                        $("#locked-text").hide();
                        $("#locked-since").hide();
                        $("#password-symbol").hide();
                        $("#timer-symbol").hide();
                        $("#time-left").hide();
                        $("#open-time").hide();
                        $("#label-choose-lock").hide();
                    }

                    // Status-Nachricht
                    var msg = "";
                    if (json.appActive == 1) {
                        if (json.appLoggedIn == 1) {
                            if (json.boxName != 0) {
                                msg = "LMB " + json.boxName;
                            } else {
                                msg = "Connect app to your LockMeBox.";
                            }
                        } else {
                            msg = "Open app and login.";
                        }
                    } else {
                        msg = "Open app for remote control.";
                    }
                    $("#status-message").text(msg);

                }
            });
        }

        setInterval(function(){
            getData();
        }, 2000);
    });
}