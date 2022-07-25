
<?php
require_once("config.php");
$sql = "INSERT INTO users (username, password, email) VALUES ('Lukas','Lukas@web.de,'hallo123)";
         
$result = mysqli_query($link, $sql); 
if ( $result === false ) {
    echo mysqli_error($link);
    exit;
}

?>