<div id="random-timer-dialog" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.25); border-radius:16px; padding:24px; width:320px;">

        <!-- Titel -->
        <p style="text-align:center; color:var(--primary-color); font-size:15px; margin-bottom:18px;">
            Lock by Random Timer
        </p>

        <!-- Minimum Time -->
        <p style="color:var(--primary-color); font-size:13px; margin-bottom:6px;">Minimum time</p>
        <div style="display:flex; align-items:center; gap:6px; margin-bottom:12px;">
            <input type="number" id="rt-min-days" min="0" max="365" value="0"
                style="width:100%; padding:10px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); text-align:center; box-sizing:border-box;">
            <span style="color:var(--primary-color); font-size:12px; white-space:nowrap;">d</span>

            <input type="number" id="rt-min-hours" min="0" max="23" value="0"
                style="width:100%; padding:10px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); text-align:center; box-sizing:border-box;">
            <span style="color:var(--primary-color); font-size:12px; white-space:nowrap;">h</span>

            <input type="number" id="rt-min-minutes" min="0" max="59" value="0"
                style="width:100%; padding:10px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); text-align:center; box-sizing:border-box;">
            <span style="color:var(--primary-color); font-size:12px; white-space:nowrap;">m</span>
        </div>

        <!-- Maximum Time -->
        <p style="color:var(--primary-color); font-size:13px; margin-bottom:6px;">Maximum time</p>
        <div style="display:flex; align-items:center; gap:6px; margin-bottom:20px;">
            <input type="number" id="rt-max-days" min="0" max="365" value="1"
                style="width:100%; padding:10px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); text-align:center; box-sizing:border-box;">
            <span style="color:var(--primary-color); font-size:12px; white-space:nowrap;">d</span>

            <input type="number" id="rt-max-hours" min="0" max="23" value="0"
                style="width:100%; padding:10px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); text-align:center; box-sizing:border-box;">
            <span style="color:var(--primary-color); font-size:12px; white-space:nowrap;">h</span>

            <input type="number" id="rt-max-minutes" min="0" max="59" value="0"
                style="width:100%; padding:10px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); text-align:center; box-sizing:border-box;">
            <span style="color:var(--primary-color); font-size:12px; white-space:nowrap;">m</span>
        </div>

        <!-- Checkbox -->
        <label style="color:var(--primary-color); font-size:13px; display:flex; gap:8px; margin-bottom:12px;">
            <input type="checkbox" id="rt-checkbox">
            <span>I confirm that the box will open at a random time within the selected range. There is no unlock option before that.</span>
        </label>

        <!-- Error -->
        <p id="rt-error" style="display:none; color:#FF6B6B; font-size:12px; margin-bottom:10px;"></p>

        <!-- Buttons -->
        <div style="display:flex; justify-content:center; gap:12px; margin-top:22px;">
            <button type="button" onclick="closeRandomTimerDialog()" class="btn-dialog">Cancel</button>
            <button type="button" onclick="submitRandomTimerDialog()" class="btn-dialog btn-dialog-primary">Lock</button>
        </div>

    </div>
</div>