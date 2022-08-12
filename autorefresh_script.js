 function refreshData(name){
 // Attention: if something is changed in this document websession has to be reset!
 // this script file is loaded in from header file!
$(document).ready(function(){
    function getData(){
        $.ajax({
            type: 'POST',
            data: {username: name},
            url: 'autorefresh.php',
            success: function(data){
             
               var json = JSON.parse(data);
    
               var LockStatusOld = localStorage.getItem("LockStatusOld");
               var conStatusOld = localStorage.getItem("conStatusOld");
               var appLoggedInOld = localStorage.getItem("appLoggedInOld");
               var AppActiveOld = localStorage.getItem("AppActiveOld");
               var OpenTimeOld = localStorage.getItem("OpenTimeOld");

                if (   (LockStatusOld  != json.LockStatus) 
                    || (conStatusOld   != json.conStatus)
                    || (appLoggedInOld != json.appLoggedIn)
                    || (AppActiveOld   != json.AppActive) 
                    || (OpenTimeOld    != json.OpenTime)
                    )
                {
                    // store old variables
                    localStorage.setItem("LockStatusOld", json.LockStatus);
                    localStorage.setItem("conStatusOld", json.conStatus);
                    localStorage.setItem("appLoggedInOld", json.appLoggedIn);
                    localStorage.setItem("OpenTimeOld", json.OpenTime);
                    localStorage.setItem("AppActiveOld", json.AppActive);

                    if (localStorage.getItem("variablesInitialized") == 1){
                        // refresh page when lockstatus/conStatus/AppLoggedIn has changed
                        window.location.href = window.location.href;
                    }
                    localStorage.setItem("variablesInitialized", 1);
                }
                
            }
        });
    }
    //getData();
    
    setInterval(function () {
        getData(); 
    }, 1000);  // it will refresh your data every 1 sec
    
});



}
