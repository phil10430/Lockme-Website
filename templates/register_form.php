<div class="overlay-card">

    <img class="bg-image" src="pictures/icon_box_unclear.png" alt="Background">

    <div class="card-content">

        <div class="login-card">

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

           <div class="form-group">
                <input 
                        type="text" 
                        name="username" 
                        class="form-control clean-input <?php echo (!empty($errors['username'])) ? 'is-invalid' : ''; ?>" 
                        value="<?php echo $username; ?>" 
                        placeholder="Username" 
                        required
                    >
                    <span class="invalid-feedback"><?php echo $errors['username']; ?></span>
                </div>

                <div class="form-group">
                    <input 
                        type="email" 
                        name="email" 
                        class="form-control clean-input <?php echo (!empty($errors['email'])) ? 'is-invalid' : ''; ?>" 
                        value="<?php echo $email; ?>" 
                        placeholder="E-Mail" 
                        required
                    >
                    <span class="invalid-feedback"><?php echo $errors['email']; ?></span>
                </div>

                <div class="form-group">
                    <input 
                        type="password" 
                        name="password" 
                        class="form-control clean-input <?php echo (!empty($errors['password'])) ? 'is-invalid' : ''; ?>" 
                        placeholder="Password" 
                        required
                    >
                    <span class="invalid-feedback"><?php echo $errors['password']; ?></span>
                </div>

                <div class="form-group">
                    <input 
                        type="password" 
                        name="confirm_password" 
                        class="form-control clean-input <?php echo (!empty($errors['confirm_password'])) ? 'is-invalid' : ''; ?>" 
                        placeholder="Confirm Password" 
                        required
                    >
                    <span class="invalid-feedback"><?php echo $errors['confirm_password']; ?></span>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-warning btn-round w-100" name="submitRegister">Register</button>
                </div>


            </form>


        </div>

    </div>
</div>


