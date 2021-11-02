

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Accept" name="acceptMasterRequest">
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Decline" name="declineMasterRequest">
    </div>
    
</form>
