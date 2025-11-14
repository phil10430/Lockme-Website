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
    
               var lockStatusOld    = localStorage.getItem("lockStatusOld");
               var conStatusOld     = localStorage.getItem("conStatusOld");
               var appLoggedInOld   = localStorage.getItem("appLoggedInOld");
               var appActiveOld     = localStorage.getItem("appActiveOld");
               var openTimeOld      = localStorage.getItem("openTimeOld");

                if (   (lockStatusOld  != json.lockStatus) 
                    || (conStatusOld   != json.conStatus)
                    || (appLoggedInOld != json.appLoggedIn)
                    || (appActiveOld   != json.appActive) 
                    || (openTimeOld    != json.openTime)
                    )
                {
                    // store old variables
                    localStorage.setItem("lockStatusOld",   json.lockStatus);
                    localStorage.setItem("conStatusOld",    json.conStatus);
                    localStorage.setItem("appLoggedInOld",  json.appLoggedIn);
                    localStorage.setItem("appActiveOld",    json.appActive);
                    localStorage.setItem("openTimeOld",     json.openTime);
                    
                    if (localStorage.getItem("variablesInitialized") == 1){
                        // refresh page when lockStatus/conStatus/appLoggedIn has changed
                        window.location.href = window.location.href;
                    }
                    localStorage.setItem("variablesInitialized", 1);
                }
                
            }
        });
    }
    
    setInterval(function () {
        getData(); 
    }, 1000);  // it will refresh your data every 1 sec
    
});



}


