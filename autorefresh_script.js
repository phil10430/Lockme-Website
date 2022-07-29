 function refreshData(name){
 
$(document).ready(function(){
    function getData(){
        $.ajax({
            type: 'POST',
            data: {username: name},
            url: 'autorefresh.php',
            success: function(data){
               // output data to output container div
               var json = JSON.parse(data);
               // $('#output').html(json.LockStatus);
               // output data in div-element with id = "LockStatus"
               // document.getElementById("LockStatus").innerHTML = data;
               var LockStatusOld = localStorage.getItem("LockStatusOld");
               var conStatusOld = localStorage.getItem("conStatusOld");
               var appLoggedInOld = localStorage.getItem("appLoggedInOld");

                if ((LockStatusOld != json.LockStatus) 
                    || (conStatusOld != json.conStatus)
                    || (appLoggedInOld != json.appLoggedIn))
                {
                    // store old variables
                    localStorage.setItem("LockStatusOld", json.LockStatus);
                    localStorage.setItem("conStatusOld", json.conStatus);
                    localStorage.setItem("appLoggedInOld", json.appLoggedIn);
                   
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