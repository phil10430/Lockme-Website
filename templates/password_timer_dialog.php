<div id="password-timer-dialog" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.25); border-radius:16px; padding:24px; width:320px;">

        <!-- Titel -->
        <p style="text-align:center; color:var(--primary-color); font-size:15px; margin-bottom:18px;">Lock by Password &amp; Timer</p>

        <!-- Password -->
        <input type="password" id="pt-password" placeholder="Password"
            style="width:100%; padding:12px; margin-bottom:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); box-sizing:border-box;">

        <!-- Confirm Password -->
        <input type="password" id="pt-confirm-password" placeholder="Confirm Password"
            style="width:100%; padding:12px; margin-bottom:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); box-sizing:border-box;">

        <!-- Datum -->
        <input type="date" id="pt-date"
            style="width:100%; padding:12px; margin-bottom:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); box-sizing:border-box;">

        <!-- Zeit -->
        <input type="time" id="pt-time"
            style="width:100%; padding:12px; margin-bottom:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); box-sizing:border-box;">

        <!-- Checkbox -->
        <label style="color:var(--primary-color); font-size:13px; display:flex; gap:8px; margin-bottom:12px;">
            <input type="checkbox" id="pt-confirm-checkbox">
            I confirm that I entered the correct password, date and time. There is no unlock option before that.
        </label>

        <!-- Error -->
        <p id="pt-error" style="display:none; color:#FF6B6B; font-size:12px; margin-bottom:10px;"></p>

        <!-- Buttons -->
        <div style="display:flex; justify-content:center; gap:10px; margin-top:6px;">
            <button onclick="closePasswordTimerDialog()" class="btn btn-round">Cancel</button>
            <button onclick="submitPasswordTimerDialog()" class="btn btn-round" style="font-weight:bold;">Lock</button>
        </div>

    </div>
</div>