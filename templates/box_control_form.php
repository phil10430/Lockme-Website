<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    
    <input type="hidden" id="password" name="password">
    <input type="hidden" id="openTimeField" name="openTime">

    <div class="boxcontrol-elements">


            <!--OPEN -->
            <button type="submit" name="openBox" class="btn btn-round w-100" id="openBox">OPEN</button>

            <!--OPEN with PW -->
            <button type="button" onclick="openOpenDialog()" class="btn btn-round w-100" id="open-box-pw-btn">OPEN</button>
            <button type="submit" name="openBoxWithPw" id="openBoxWithPw" style="display:none;"></button>
       
            <!-- Mit Passwort -->
            <button type="button" onclick="openPasswordDialog()" class="btn btn-round w-100">CLOSE PW</button>
            <button type="submit" name="closeBoxWithPw" id="closeBoxWithPw" style="display:none;"></button>

            <!-- Mit Timer -->
            <button type="button" onclick="openTimerDialog()" class="btn btn-round w-100">CLOSE TIMER</button>
            <button type="submit" name="closeBoxWithTimer" id="closeBoxWithTimer" style="display:none;"></button>

            <!-- Mit Passwort und Timer -->
            <button type="submit" name="closeBoxPwTimer" class="btn btn-round w-100">CLOSE PW+TIMER</button>
        
    </div>
</form>