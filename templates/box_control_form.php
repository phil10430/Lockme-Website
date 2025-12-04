<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

 <div class="boxcontrol-elements">

    <div class="box a">
        <div class="input-group date">
                    <input type="text"
                        class="form-control clean-input"
                        id="openTimeField"
                        name="openTime"
                    placeholder="Timer">
        </div>
    </div>
    

    <div class="box b">

        <?php if ($lockStatus == 1) { ?>
                
                        <button type="submit"
                                name="setTimer"
                                class="btn btn-round w-100">
                            SET TIMER
                        </button>
                
        <?php } ?>

    </div>


    <div class="box c">
        <input type="text"
                            class="form-control clean-input"
                            id="password"
                            name="password"
                            maxlength="10"
                            placeholder="Password">
    </div>



    <div class="box d">
        <button type="submit"
                                name="closeBox"
                                class="btn btn-round w-100">
                            <?= htmlspecialchars($closeButtonText) ?>
        </button>

    </div>


</div>

</form>