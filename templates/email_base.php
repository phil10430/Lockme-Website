<?php
// expects: $title, $content
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
        }

        .header {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            padding: 12px 20px;
            background: #4CAF50;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
    </style>
</head>
<body>

<div class="container">

   <div class="header" style="text-align:center; margin-bottom:24px;">

    <a href="https://lockmebox.com" target="_blank" style="text-decoration:none;">

        <img 
            src="https://lockmebox.com/assets/images/lmb_logo_text.png"
            alt="LockMeBox"
            style="
                width:120px;
                height:auto;
                display:block;
                margin:0 auto 18px auto;
                border:0;
            "
        />

    </a>

    <div style="
        font-size:20px;
        font-weight:bold;
        line-height:1.3;
        color:#111;
    ">
        <?= htmlspecialchars($title) ?>
    </div>

    </div>

    <div class="content">
        <?= $content ?>
    </div>

    <div class="footer">
        © <?= date("Y") ?> LockMeBox · All rights reserved<br>
        This is an automated message, please do not reply.
    </div>

</div>

</body>
</html>