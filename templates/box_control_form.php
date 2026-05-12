<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    
    <input type="hidden" id="password" name="password">
    <input type="hidden" id="openTimeField" name="openTime">

<div class="boxcontrol-elements">
    <!-- OPEN Row -->
    <div style="grid-column: 1 / -1; display:flex; justify-content:center;">
        <button type="submit" name="openBox" class="btn btn-round" id="openBox">OPEN</button>
        <button type="button" onclick="openOpenDialog()" class="btn btn-round" id="open-box-pw-btn">OPEN</button>
        <button type="submit" name="openBoxWithPw" id="openBoxWithPw" style="display:none;"></button>
    </div>

    <!-- ADD TIME Row -->
    <div style="grid-column: 1 / -1; display:flex; justify-content:center;">
        <button type="button" onclick="openExtendTimeDialog()" class="btn btn-round" id="extend-time-btn"style="width:80px;">
                <img src="/assets/images/lockme_symbol_add_time_white.png" style="width:30px; height:20px; object-fit:contain;">
        </button>
        <button type="submit" name="extendTime" id="extendTime" style="display:none;"></button>
    </div>

    <!-- CLOSE Label -->
    <div id="label-choose-lock"  style="grid-column: 1 / -1; text-align:center;">
        <span style="font-size:13px; color:var(--primary-color);">Choose how to lock</span>
    </div>

    <!-- CLOSE Row 1 -->
    <div style="grid-column: 1 / -1; display:flex; justify-content:center; gap:10px;">
        <button type="button" onclick="lockDialog('password')" class="btn btn-round" id="close-box-pw-btn"style="width:80px;">
            <img src="/assets/images/lmb_symbol_pw_white.png" style="width:30px; height:24px; object-fit:contain;">
        </button>
        <button type="submit" name="closeBoxWithPw" id="closeBoxWithPw" style="display:none;"></button>

        <button type="button" onclick="lockDialog('timer')" class="btn btn-round" id="close-box-timer" style="width:80px;">
            <img src="/assets/images/lmb_symbol_timer_white.png" style="width:20px; height:24px; object-fit:contain;">
        </button>
        <button type="submit" name="closeBoxWithTimer" id="closeBoxWithTimer" style="display:none;"></button>
    </div>

    <!-- CLOSE Row 2 -->
    <div style="grid-column: 1 / -1; display:flex; justify-content:center;">
        <button type="button" onclick="lockDialog('passwordTimer')" class="btn btn-round" id="close-box-pwtimer" style="width:80px;">
            <img src="/assets/images/lmb_symbol_timerpw_white.png" style="width:40px; height:24px; object-fit:contain;">
        </button>
        <button type="submit" name="closeBoxWithPasswordTimer" id="closeBoxWithPasswordTimer" style="display:none;"></button>
    </div>
</div>
</form>