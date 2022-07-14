
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Close" name="CloseBox">
        <input type="submit" class="btn btn-primary" value="Open" name="OpenBox">
        <label class="control-label">Password</label>
        <input type="text" name="Password"><br>
    </div>
     <!-- DateTimePicker -->
       <label class="control-label">Open Time</label>
            <div class='input-group date' id='datetimepicker1'>
                <input type='text' class="form-control" name="OpenTime"/>
                <span class="input-group-addon">
                <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
     <!-- DateTimePicker -->

     <input type="radio" name="protectionLevelRadioGroup" id="timeRadioID" value="timeRadio" />
    <label for="timeRadioID">Time Protection</label>

    <input type="radio" name="protectionLevelRadioGroup" id="passwordRadioID" value="passwordRadio" />
    <label for="passwordRadioID">Password Protection</label>
</form>
    
</form>
