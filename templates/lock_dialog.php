<div id="lock-dialog" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.25); border-radius:16px; padding:24px; width:320px;">

        <p id="lock-dialog-title" style="text-align:center; color:var(--primary-color); font-size:15px; margin-bottom:18px;"></p>

        <div id="lock-dialog-date-group">
            <input type="date" id="lock-dialog-date"
                style="width:100%; padding:12px; margin-bottom:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); box-sizing:border-box;">
        </div>

        <div id="lock-dialog-time-group">
            <input type="time" id="lock-dialog-time"
                style="width:100%; padding:12px; margin-bottom:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); box-sizing:border-box;">
        </div>

        <div id="lock-dialog-password-group">
            <div style="position:relative; margin-bottom:12px;">
                <input type="password" id="lock-dialog-password" placeholder="Password"
                    style="width:100%; padding:12px; padding-right:40px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); box-sizing:border-box;">
                <span onclick="togglePassword('lock-dialog-password', this)"
                    style="position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--primary-color); opacity:0.6;">
                    <i class="ti ti-eye" style="font-size:18px;"></i>
                </span>
            </div>
        </div>

        <div id="lock-dialog-confirm-group">
            <div style="position:relative; margin-bottom:12px;">
                <input type="password" id="lock-dialog-confirm" placeholder="Confirm Password"
                    style="width:100%; padding:12px; padding-right:40px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); box-sizing:border-box;">
                <span onclick="togglePassword('lock-dialog-confirm', this)"
                    style="position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--primary-color); opacity:0.6;">
                    <i class="ti ti-eye" style="font-size:18px;"></i>
                </span>
            </div>
        </div>

        <label style="color:var(--primary-color); font-size:13px; display:flex; gap:8px; margin-bottom:12px;">
            <input type="checkbox" id="lock-dialog-checkbox">
            <span id="lock-dialog-checkbox-label">I confirm that I understand. There is no unlock option before that.</span>
        </label>

        <p id="lock-dialog-error" style="display:none; color:#FF6B6B; font-size:12px; margin-bottom:10px;"></p>

        <div style="display:flex; gap:12px; margin-top:22px;">
            <button type="button" onclick="closeLockDialog()" class="btn-dialog">Cancel</button>
            <button type="button" id="lock-dialog-btn-submit" onclick="submitLockDialog()" class="btn-dialog btn-dialog-primary">Lock</button>
        </div>

    </div>
</div>