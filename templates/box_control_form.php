<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    
    <input type="hidden" id="password" name="password">
    <input type="hidden" id="openTimeField" name="openTime">
    <input type="hidden" id="openTimeRandomField" name="openTimeRandom">

<div class="boxcontrol-elements">
    <!-- OPEN Row -->
    <div style="grid-column: 1 / -1; display:flex; justify-content:center;">
        <?php // OPEN without password: name "openBox" ist post key der im php geprüft wird ?>
        <button type="submit" name="openBox" class="btn btn-round" id="openBox">OPEN</button>
         <?php // OPEN with password: öffnet Dialog, wird im JS über id="open-box-pw-btn" referenziert ?>
        <button type="button" onclick="openOpenDialog()" class="btn btn-round" id="open-box-pw-btn" style="width:80px;">
            <img src="/assets/images/icon_open.png" style="width:30px; height:20px; object-fit:contain;">
        </button>
         <?php //  Versteckter submit button: wird per JS geklickt $("#openBoxWithPw").click()?>
         <?php //  name="openBoxWithPw" ist der POST-Key, id="openBoxWithPw" ist der JS-Selektor ?>
        <button type="submit" name="openBoxWithPw" id="openBoxWithPw" style="display:none;"></button>
    </div>

    <!-- EXTEND TIME Row -->
    <div style="grid-column: 1 / -1; display:flex; justify-content:center;">
        <button type="button" name= "extendTime" onclick="lockDialog('extendTime')" class="btn btn-round" id="extend-time-btn" style="width:80px;">
                <img src="/assets/images/icon_extend_timer.png" style="width:30px; height:20px; object-fit:contain;">
        </button>
        <button type="submit" name="extendTime" id="extendTime" style="display:none;"></button>
    </div>

    <!-- CLOSE Label -->
    <div id="label-choose-lock"  style="grid-column: 1 / -1; text-align:center;">
        <span class="choose-lock-text">Choose how to lock</span>
    </div>

    <!-- CLOSE Row 1 -->
    <div style="grid-column: 1 / -1; display:flex; justify-content:center; gap:20px;">
        <button type="button" onclick="lockDialog('password')" class="btn btn-round" id="close-box-pw-btn"style="width:80px;">
            <img src="/assets/images/icon_password.png" style="width:30px; height:24px; object-fit:contain;">
        </button>
        <button type="submit" name="closeBoxWithPw" id="closeBoxWithPw" style="display:none;"></button>

        <button type="button" onclick="lockDialog('timer')" class="btn btn-round" id="close-box-timer" style="width:80px;">
            <img src="/assets/images/icon_timer.png" style="width:20px; height:24px; object-fit:contain;">
        </button>
        <button type="submit" name="closeBoxWithTimer" id="closeBoxWithTimer" style="display:none;"></button>
    </div>

    <!-- CLOSE Row 2 -->
    <div style="grid-column: 1 / -1; display:flex; justify-content:center;  gap:20px;">

        <button type="button" onclick="lockDialog('passwordTimer')" class="btn btn-round" id="close-box-pwtimer" style="width:120px;">
            <img src="/assets/images/icon_password_timer.png" style="width:65px; height:30px; object-fit:contain;">
        </button>
        <button type="submit" name="closeBoxWithPasswordTimer" id="closeBoxWithPasswordTimer" style="display:none;"></button>


         <button type="button" onclick="openRandomTimerDialog()" class="btn btn-round" id="close-box-randtimer" style="width:80px;">
            <img src="/assets/images/icon_random_timer.png" style="width:20px; height:24px; object-fit:contain;">
        </button>

        <button type="submit" name="closeBoxWithRandomTimer" id="closeBoxWithRandomTimer" style="display:none;"></button>

    </div>
</div>
</form>