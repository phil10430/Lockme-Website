<div id="open-dialog" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.25); border-radius:16px; padding:24px; width:320px;">

        <!-- Titel -->
        <p style="text-align:center; color:var(--primary-color); font-size:18px; margin-bottom:18px;">Open your LockMeBox</p>

        <!-- Password -->
        <input type="password" id="open-password"
            style="width:100%; padding:12px; margin-bottom:12px; border-radius:12px; border:1px solid rgba(255,255,255,0.25); background:rgba(255,255,255,0.12); color:var(--primary-color); box-sizing:border-box;"
            placeholder="Password">

        <!-- Error -->
        <p id="open-error" style="display:none; color:#FF6B6B; font-size:12px; margin-bottom:10px;"></p>

        <!-- Buttons -->
       <div style="display:flex; gap:12px; margin-top:22px;">
            <button type="button" onclick="closeOpenDialog()" class="btn-dialog">Cancel</button>
            <button type="button" onclick="submitOpenDialog()" class="btn-dialog btn-dialog-primary">Open</button>
        </div>
    </div>
</div>