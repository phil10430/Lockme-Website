<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/templates/header.php'; 

// Passwort-Reset-Logik
require_once __DIR__ . '/password_reset.php'; 
?>

<div class="card">

    <div class="card-header">
        Reset your password:
    </div>

    <div class="overlay-card">
        <img class="bg-image" src="/assets/images/icon_box_unclear.png" alt="Background">

        <div class="card-content">
            <div class="passwordreset-card">

                <form action="" method="POST">

                    <!-- Error Messages -->
                    <?php if (!empty($error)): ?>
                        <?php foreach ($error as $msg): ?>
                            <div class="alert alert-danger mb-2"><?= htmlspecialchars($msg) ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Success Message -->
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success mb-3"><?= $success ?></div>
                    <?php endif; ?>

                    <!-- Formular nur anzeigen, wenn $hide = 0 -->
                    <?php if (empty($hide)): ?>

                        <!-- New Password -->
                        <div class="form-group">
                            <input 
                                type="password" 
                                name="password" 
                                class="form-control clean-input <?= (!empty($error)) ? 'is-invalid' : '' ?>"
                                placeholder="New Password"
                                required
                            >
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group">
                            <input 
                                type="password" 
                                name="passwordConfirm" 
                                class="form-control clean-input <?= (!empty($error)) ? 'is-invalid' : '' ?>"
                                placeholder="Confirm Password"
                                required
                            >
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group">
                            <button 
                                type="submit" 
                                name="sub_set" 
                                class="btn btn-round w-100"
                            >
                                RESET PASSWORD
                            </button>
                        </div>

                    <?php endif; ?>

                </form>

            </div>
        </div>
    </div>

    <div class="card-footer">
        <p>Have an account? <a href="index.php">Login</a></p>
        <p>Don't have an account? <a href="register_page.php">Sign up</a></p>
    </div>

</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
