<?php require "login.php";?>


<!DOCTYPE html>
<html lang="en">

<head>
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width; initial-scale=1.0;">
    <title>LockMe</title>
    <script src="https://code.jquery.com/jquery-1.10.2.js"></script>

     <!-- DateTimePicker -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/2.14.1/moment.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="myStyle.css"> 
    <!-- DateTimePicker -->
</head>

<body>

    <div id="nav-placeholder"></div>

    <script>
        $(function() {
            $("#nav-placeholder").load("navbar.html");
            $("#footer-placeholder").load("footer.html");
            $('#datetimepicker1').datetimepicker({
                minDate : new Date(),
                format : 'DD/MM/YYYY HH:mm',
            });
        });
    </script>

    <div class="main">

        <?php  if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {?>
             <!-- HTML here -->
             <h1 class="my-5">Hello <?php echo htmlspecialchars($_SESSION["role"]); ?> <?php echo htmlspecialchars($_SESSION["username"]); ?></h1>
            
             <?php  
             if ($_SESSION["role"] == "Master" ){
                require "setup_master.php";
                require "setup_master_form.php";
             }
             else {
                require "setup_slave.php";
             }
             require "box_control.php";
             require "show_history.php";
             ?>
         
            <p>
                <a href="reset-password.php" class="btn btn-warning">Reset Your Password</a>
            </p>
            <p>
                <a href="logout.php" class="btn btn-danger ml-3">Sign Out of Your Account</a>
            </p>

        <?php } else { require "login_form.php";}?>
    </div>

    <div id="footer-placeholder" class="footer"></div>

</body></html>