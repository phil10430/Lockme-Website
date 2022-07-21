<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

    <div class="row form-group">
        <div class="col-sm">
            Connected to <?php echo $BoxName ?>
        </div>
    </div>

    <div class="row form-group">
        <div class="col-2">
            <input type="submit" class="btn btn-primary" value="Close" name="CloseBox">
        </div>
        <div class="col-2">
            <input type="submit" class="btn btn-primary" value="Open" name="OpenBox">
        </div>
    </div>


    <div class="row form-group">
        <div class="col-2">
            <div class="form-check">
                <input type="checkbox" name="timeCheckbox" id="timeCheckboxID" value="timeCheckbox" />
                <label for="timeRadioID">Open Time</label>
            </div>
        </div>
        <div class="col-2">
            <div class='input-group date' id='datetimepicker1'>
                <input type='text' class="form-control" name="OpenTime" />
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
        </div>
    </div>

    <div class="row form-group">
        <div class="col-2">
            <div class="form-check">
                <input type="checkbox" name="passwordCheckbox" id="passwordCheckboxID" value="passwordCheckbox" />
                <label for="passwordRadioID">Password</label>
            </div>
        </div>
        <div class="col-2">
            <input type="text" maxlength='10' id="Password" name="Password"><br>
        </div>
    </div>
  

</form>