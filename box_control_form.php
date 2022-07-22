<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
<div class="text-center">
    <div class="btn-group" role="group" aria-label="Basic mixed styles example">
        <button type="submit" name="OpenBox" class="btn btn-success">Open</button>
        <button type="submit" name="CloseBox" class="btn btn-warning">Close</button>   
    </div>
</div>
    
        <div class="row">
            <div class="col-md-12">
                <div class="input-group">
                    <span class="input-group-addon">
                        <input type="checkbox" name="timeCheckbox" id="timeCheckboxID" value="timeCheckbox" />
                    </span>
                    <span class="input-group-addon">
                        <label for="tbox">Open Time</label>
                    </span>
                    <div class='input-group date' id='datetimepicker1'>
                        <input type='text' class="form-control" name="OpenTime" />
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                    </div>

                </div>
            </div>
        </div>
    
        <div class="row">
            <div class="col-md-12">
                <div class="input-group">
                    <span class="input-group-addon">
                        <input type="checkbox" name="passwordCheckbox" id="passwordCheckboxID" value="passwordCheckbox" />
                    </span>
                    <span class="input-group-addon">
                        <label for="tbox">Password</label>
                    </span>
                    <input type="text" class="form-control" maxlength='10' id="Password" name="Password"><br>
                </div>
            </div>
        </div>
   
</form>