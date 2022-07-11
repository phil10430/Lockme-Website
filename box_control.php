<?php
    require_once "config.php";
    $close = "C0";
    $open = "O";
    $name = $_SESSION["username"]; 
    // Processing form data when form is submitted
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if (isset($_POST['OpenBox'])) {
            $sql = "UPDATE users SET WishedAction='$open' WHERE username='$name'";
            echo "open box";
        }
        else if (isset($_POST['CloseBox'])) {
            $sql = "UPDATE users SET WishedAction='$close' WHERE username='$name'";
            echo "close box";
        }
        $link->query($sql);
       
    } 
?>