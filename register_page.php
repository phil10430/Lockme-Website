<?php require  "config.php"; ?>
<?php require  "register.php"; ?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LockMe</title>
    <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
    <link rel="stylesheet" href="myStyle.css">
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VNV01ML37L"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-VNV01ML37L');
    </script>

</head>

<body>


    <div id="nav-placeholder"></div>

    <script>
        $(function() {
            $("#footer-placeholder").load("footer.html");
        });
    </script>

    <div class="main">
        <?php require  "register_form.php"; ?>
    </div>

    <div id="footer-placeholder" class="footer"></div>


</body>

</html>