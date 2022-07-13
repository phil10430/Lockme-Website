<?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="form-group">
        <label>Add slaves name</label>
        <input type="text" name="slavename" class="form-control <?php echo (!empty($slavename_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $slavename; ?>">
        <span class="invalid-feedback"><?php echo $slavename_err; ?></span>
        <input type="submit" class="btn btn-primary" value="Send request" name="submitRequestData">
    </div>    

     
    
</form>
