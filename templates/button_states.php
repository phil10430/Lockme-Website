<?php
$displayOpenBox      = "none";
$displayOpenBoxPw    = "none";
$displayClosePw      = "none";
$displayCloseTimer   = "none";
$displayClosePwTimer = "none";
$displayCloseRandomTimer = "none";
$displayLabelChooseLock = "none";
$displayExtendTime = "none";

if ($appLoggedIn==1 && $boxName!=0 && $appActive==1) {
    
    if ($lockStatus == 1) {
        if ($protectionLevelPassword == 1) {
            $displayOpenBoxPw = "block";
        } else {
            $displayOpenBox = "block";
        }
        if ($protectionLevelTimer == 1){
            $displayExtendTime = "block";
        }
    } else {
        $displayClosePw             = "block";
        $displayCloseTimer          = "block";
        $displayClosePwTimer        = "block";
        $displayCloseRandomTimer    = "block";
        $displayLabelChooseLock     = "block";  
    }
}
?>
<style>
    #openBox           { display: <?= $displayOpenBox ?>; }
    #extend-time-btn   { display: <?= $displayExtendTime ?>; }
    #open-box-pw-btn   { display: <?= $displayOpenBoxPw ?>; }
    #close-box-pw-btn  { display: <?= $displayClosePw ?>; }
    #close-box-timer   { display: <?= $displayCloseTimer ?>; }
    #close-box-pwtimer  { display: <?= $displayClosePwTimer ?>; }
    #close-box-randtimer { display: <?= $displayCloseRandomTimer ?>; }
    #label-choose-lock { display: <?= $displayLabelChooseLock ?>; } 
</style>