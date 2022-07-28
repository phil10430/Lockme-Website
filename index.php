<?php require "login.php"; ?>
<?php require "header.php"; ?>


<section class="container" id="main">
    <nav class="navbar navbar-light bg-light">
        <a class="navbar-brand" href="#">
            <img src="pictures/logo.png" width="30" height="30" class="d-inline-block align-center" alt="">
            LockMe
        </a>
    </nav>
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

            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

            <?php 
             //get lockstatus
            $username = $_SESSION["username"];
            $result = mysqli_query($link, "SELECT LockStatus FROM users WHERE username = '$username' ");
            while ($row = $result->fetch_assoc()) {
                $LockStatus =  $row['LockStatus'];
            }
            ?>
           
        
            <script>
                var oldLockStatus = <?php echo $LockStatus?>;
                var name = "<?php echo $username; ?>";
                $(document).ready(function(){
                    function getData(){
                        $.ajax({
                            type: 'POST',
                            data: {username: name},
                            url: 'autorefresh.php',
                            success: function(data){
                               // output data to output container div
                               // $('#output').html(data);
                               // output data in div-element with id = "LockStatus"
                               // document.getElementById("LockStatus").innerHTML = data;
                                if (oldLockStatus != data) 
                                {
                                    // refresh page when lockstatus has changed
                                     window.location.href = window.location.href;
                                }
                                
                            }
                        });
                    }
                    setInterval(function () {
                        getData(); 
                    }, 1000);  // it will refresh your data every 1 sec

                });
            </script>

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