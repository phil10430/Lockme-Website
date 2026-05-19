function openApp() {
    var appUrl   = "lockmebox://open";
    var fallback = "https://play.google.com/store/apps/details?id=com.kinkystuffmade.lockmebox&hl=de";
    
    setTimeout(function() {
        window.location = fallback;
    }, 1500);
    
    window.location = appUrl;
}