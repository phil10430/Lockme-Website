<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    
    <input type="hidden" id="password" name="password">
    <input type="hidden" id="openTimeField" name="openTime">

 <div class="boxcontrol-elements">

    <!--OPEN -->
    <button type="submit" name="openBox" class="btn btn-round w-100" id="openBox">OPEN</button>

    <!--OPEN with PW -->
    <!-- visible button in php form -->
    <button type="button" onclick="openOpenDialog()" class="btn btn-round w-100" id="open-box-pw-btn">OPEN PW</button>
    <!-- versteckter Submit-Button, wird per JS geklickt wenn der User im Dialog auf "Open" drückt, sendet das Form an PHP -->
    <button type="submit" name="openBoxWithPw" id="openBoxWithPw" style="display:none;"></button>

    <!-- Mit Passwort -->
    <button type="button" onclick="openPasswordDialog()" class="btn btn-round w-100" id="close-box-pw-btn">
        <img src="/assets/images/lmb_symbol_pw_white.png" 
            style="width:30px; height:24px; object-fit:contain;">
    </button>
    <button type="submit" name="closeBoxWithPw" id="closeBoxWithPw" style="display:none;"></button>

    <!-- Mit Timer -->
    <button type="button" onclick="openTimerDialog()" class="btn btn-round w-100" id="close-box-timer">
         <img src="/assets/images/lmb_symbol_timer_white.png" 
            style="width:20px; height:24px; object-fit:contain;">
    </button>

    <button type="submit" name="closeBoxWithTimer" id="closeBoxWithTimer" style="display:none;"></button>

    <!-- Mit Passwort und Timer -->
    <button type="submit" name="closeBoxPwTimer" class="btn btn-round w-100" id="close-box-pwtimer">
         <img src="/assets/images/lmb_symbol_timerpw_white.png" 
            style="width:40px; height:24px; object-fit:contain;">
    </button>

</div>
</form>