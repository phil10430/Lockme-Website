<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
 
    <div class="uiElements">  

        <!-- Timer -->
        <div class="row mb-3">
            <div class="col-md-10">
                <div class="input-group date" id="datetimepicker1">
                    <input type="text"
                        class="form-control clean-input"
                        id="openTimeField"
                        name="openTime"
                        placeholder="Timer">
                </div>
            </div>
            <?php if ($lockStatus == 1) { ?>
                <div class="col-md-2">
                    <button type="submit"
                            name="setTimer"
                            class="btn btn-round w-100">
                        SET TIMER
                    </button>
                </div>
            <?php } ?>
        </div>


        <!-- Password -->
        <div class="row mb-3">
            <div class="col-md-10">
                <input type="text"
                    class="form-control clean-input"
                    id="password"
                    name="password"
                    maxlength="10"
                    placeholder="Password">
            </div>
            <div class="col-md-2">
                <button type="submit"
                        name="closeBox"
                        class="btn btn-round w-100">
                    <?= htmlspecialchars($closeButtonText) ?>
                </button>
            </div>
        </div>

    </div>
</form>