function refreshData(name){
    $(document).ready(function(){
        function getData(){
            $.ajax({
                type: 'POST',
                data: {username: name},
                url: 'autorefresh.php',
                success: function(data){
                    var json = JSON.parse(data);

                    // Hintergrundbild & box-control-form
                    if (json.appLoggedIn==1 && json.boxName!=0 && json.appActive==1) {
                        $("#box-control-form").show();
                        if (json.lockStatus == 0) {
                            $("#bg-image").attr("src", "/assets/images/icon_box_open.png");
                            $("#openBox").hide(); 
                            $("#open-box-pw-btn").hide();
                        } else  {
                            $("#bg-image").attr("src", "/assets/images/icon_box_closed.png");
                            if (json.protectionLevelPassword == 1) {
                                $("#openBox").hide();
                                $("#open-box-pw-btn").show();
                            } else {
                                $("#openBox").show();
                                $("#open-box-pw-btn").hide();
                            }
                        }
                    } else {
                        $("#box-control-form").hide();
                        $("#bg-image").attr("src", "/assets/images/lmb_start.png");
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
            });
        }

        setInterval(function(){
            getData();
        }, 2000);
    });
}