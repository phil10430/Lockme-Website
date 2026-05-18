<!DOCTYPE html>

<html lang="en">

    <head>
        
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width; initial-scale=1.0;">
        <title>Lockmebox</title>
        <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
        
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"> 
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
        <link rel="stylesheet"  href="/assets/css/style.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script> 
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script  src="/assets/js/autorefresh_script.js"></script>    
        <script src="/assets/js/open_dialog.js"></script>
        <script src="/assets/js/lock_dialog.js"></script>
        
    </head>
       
    <body class="<?= $bodyClass ?? '' ?>">   

        <script>
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        </script>




    <!-- GLOBAL NAVBAR -->
    <nav class="topbar">

        <div class="topbar-inner">

            <!-- LEFT -->
            <div class="logo">
                <a href="/">
                    <img src="/assets/images/logo_white.png" alt="Lockmebox Logo">
                </a>
            </div>

            <!-- CENTER -->
            <div class="top-center">
                <a href="/faq.php">FAQ</a>
            </div>

            <!-- RIGHT -->
            <div class="top-right">

                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>

                    <a href="/profile_page.php" title="Account">
                        <i class="ti ti-user"></i>
                    </a>

                    <a href="/logout.php" title="Logout">
                        <i class="ti ti-logout"></i>
                    </a>

                <?php else: ?>

                    <a href="/control_center.php" title="Login">
                        <i class="ti ti-login"></i>
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </nav>

<?php if (!empty($noindex)): ?>
  <meta name="robots" content="noindex, nofollow">
<?php endif; ?>