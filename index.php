<?php require "login.php"; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- <link rel="stylesheet" href="myStyle.css">  -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width; initial-scale=1.0;">
    <title>LockMe</title>
    <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
    <!-- DateTimePicker -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

</head>

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

                <div class="card-header">
                     Hello <?php echo htmlspecialchars($_SESSION["username"]); ?>
                </div>

                <div class="card-body">
                    <?php
                    require "box_control.php";
                    require "show_history.php";
                    ?>

                </div>

                <div class="card-footer">
                    <div class="row form-group">
                        <div class="col-sm">
                            <a href="logout.php" class="btn btn-danger ml-3">Sign Out</a>
                        </div>
                    </div>
                </div>

            <?php } else{ ?>
                <div class="card-header">
                    Login to use LockMe-Box.
                </div>

                <div class="card-body">
                    <?php  require "login_form.php"; } ?>
                </div>

                <div class="card-footer">
                </div>
       
           

            <div class="card">




    </section>

</body>

</html>