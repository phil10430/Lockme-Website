<div id="timer-dialog" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.25); border-radius:16px; padding:24px; width:320px;">

        <!-- Titel -->
        <p style="text-align:center; color:var(--primary-color); font-size:15px; margin-bottom:18px;">Lock by Timer</p>

        <!-- Datum -->
        <input type="date" id="timer-date"
            style="width:100%; padding:12px; margin-bottom:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); box-sizing:border-box;">

        <!-- Zeit -->
        <input type="time" id="timer-time"
            style="width:100%; padding:12px; margin-bottom:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); box-sizing:border-box;">

        <!-- Checkbox -->
        <label style="color:var(--primary-color); font-size:13px; display:flex; gap:8px; margin-bottom:12px;">
            <input type="checkbox" id="timer-confirm-checkbox">
            I confirm that I entered the correct date and time. There is no unlock option before that.
        </label>

        <!-- Error -->
        <p id="timer-error" style="display:none; color:#FF6B6B; font-size:12px; margin-bottom:10px;"></p>

        <!-- Buttons -->
        <div style="display:flex; justify-content:center; gap:10px; margin-top:6px;">
            <button onclick="closeTimerDialog()" class="btn btn-round">Cancel</button>
            <button onclick="submitTimerDialog()" class="btn btn-round" style="font-weight:bold;">Lock</button>
        </div>
    </div>
</div>