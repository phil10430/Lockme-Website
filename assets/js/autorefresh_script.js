function refreshData(name){
    $(document).ready(function(){
        function getData(){
            $.ajax({
                type: 'POST',
                data: {username: name},
                url: 'autorefresh.php',
                success: function(data){
                    var json = JSON.parse(data);

                    var lockStatusOld    = localStorage.getItem("lockStatusOld");
                    var boxNameOld       = localStorage.getItem("boxNameOld");
                    var appLoggedInOld   = localStorage.getItem("appLoggedInOld");
                    var appActiveOld     = localStorage.getItem("appActiveOld");
                    var openTimeOld      = localStorage.getItem("openTimeOld");

                    if (   (lockStatusOld  != json.lockStatus)
                        || (boxNameOld     != json.boxName)
                        || (appLoggedInOld != json.appLoggedIn)
                        || (appActiveOld   != json.appActive)
                        || (openTimeOld    != json.openTime)
                    ) {
                        localStorage.setItem("lockStatusOld",  json.lockStatus);
                        localStorage.setItem("boxNameOld",     json.boxName);
                        localStorage.setItem("appLoggedInOld", json.appLoggedIn);
                        localStorage.setItem("appActiveOld",   json.appActive);
                        localStorage.setItem("openTimeOld",    json.openTime);

                        if (localStorage.getItem("variablesInitialized") == 1) {

                            // Hintergrundbild tauschen
                            if (json.appLoggedIn==1 && json.boxName!=0 && json.appActive==1) {
                                if (json.lockStatus == 0) {
                                    $("#bg-image").attr("src", "/assets/images/icon_box_open.png");
                                } else {
                                    $("#bg-image").attr("src", "/assets/images/icon_box_closed.png");
                                }
                            } else {
                                $("#bg-image").attr("src", "/assets/images/icon_box_unclear.png");
                            }

                            // Status-Nachricht
                            var msg = "";
                            if (json.appActive == 1) {
                                if (json.appLoggedIn == 1) {
                                    if (json.boxName != 0) {
                                        msg = "Box #" + json.boxName;
                                    } else {
                                        msg = "Connect app to your LockMeBox.";
                                    }
                                } else {
                                    msg = "Open app and login.";
                                }
                            } else {
                                msg = "Open app to enable control.";
                            }
                            $("#status-message").text(msg);

                            // locked-since
                            if (json.lockStatus == 1) {
                                $("#locked-since").show().html(json.lockedSince + "<br>");
                            } else {
                                $("#locked-since").hide();
                            }

                            // time-left & open-time
                            if (json.protectionLevelTimer == 1) {
                                $("#time-left").show().text(json.timeLeft);
                                $("#open-time").show().text(json.openTime);
                            } else {
                                $("#time-left").hide();
                                $("#open-time").hide();
                            }

                            // password symbol
                            if (json.protectionLevelPassword == 1) {
                                $("#password-symbol").show();
                            } else {
                                $("#password-symbol").hide();
                            }
                        }

                        localStorage.setItem("variablesInitialized", 1);
                    }
                }
            });
        }

        setInterval(function(){
            getData();
        }, 2000);
    });
}