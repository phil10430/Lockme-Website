<?php
$displayOpenBox      = "none";
$displayOpenBoxPw    = "none";
$displayClosePw      = "none";
$displayCloseTimer   = "none";
$displayClosePwTimer = "none";

if ($appLoggedIn==1 && $boxName!=0 && $appActive==1) {
    if ($lockStatus == 1) {
        if ($protectionLevelPassword == 1) {
            $displayOpenBoxPw = "block";
        } else {
            $displayOpenBox = "block";
        }
    } else {
        $displayClosePw      = "block";
        $displayCloseTimer   = "block";
        $displayClosePwTimer = "block";
    }
}
?>
<style>
    #openBox           { display: <?= $displayOpenBox ?>; }
    #open-box-pw-btn   { display: <?= $displayOpenBoxPw ?>; }
    #close-box-pw-btn  { display: <?= $displayClosePw ?>; }
    #close-box-timer   { display: <?= $displayCloseTimer ?>; }
    #close-box-pwtimer { display: <?= $displayClosePwTimer ?>; }
</style>