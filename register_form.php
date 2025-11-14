<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="form-group">
        <label>username</label>
        <input type="text" name="username" class="form-control <?php echo (!empty($errors['username'] )) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
        <span class="invalid-feedback"><?php echo  $errors['username'] ; ?></span>
    </div>
    <div class="form-group">
        <label>E-Mail</label>
        <input type="text" name="email" class="form-control <?php echo (!empty($errors['email'])) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
        <span class="invalid-feedback"><?php echo $errors['email']; ?></span>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-control <?php echo (!empty($errors['password'])) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
        <span class="invalid-feedback"><?php echo $errors['password']; ?></span>
    </div>
    <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($errors['confirm_password'])) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
        <span class="invalid-feedback"><?php echo $errors['confirm_password']; ?></span>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Submit">
        <input type="reset" class="btn btn-secondary ml-2" value="Reset">
    </div>

</form>