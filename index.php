<?php require "login.php"; ?>
<?php require "header.php"; ?>

<body>
    <script>
        // load datetime picker
        $(function() {
            $('#datetimepicker1').datetimepicker({
                minDate: new Date(),
                format: 'DD/MM/YYYY HH:mm',
            });
        });

        // prevent post on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>

    <section class="container" id="main">
        <div class="card">
            <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) { ?>
                <div class="text-center">
                    <div class="card-header">              
                        <h4>Hello <?php echo htmlspecialchars($_SESSION["username"]); ?></h4>
                    </div>
                </div>

                <div class="card-body">
                    <?php
                    require "box_control.php";
                    require "show_history.php";
                    ?>

                </div>

                <div class="card-footer">
                    <div class="text-center">
                        <a href="logout.php" class="btn btn-danger">Sign Out</a>
                    </div>
                </div>

            <?php } else { ?>
                <div class="card-header">
                    Login to use LockMe-Box.
                </div>
                <div class="card-body">
                    <?php require "login_form.php"; ?>
                </div>
                <div class="card-footer">
                    Don't have an account? <a href="register_page.php">Sign up now</a>.
                </div>
            <?php } ?>

        </div>
    </section>

</body>

</html>