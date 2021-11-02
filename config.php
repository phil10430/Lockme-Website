<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', 'fdb33.awardspace.net');
define('DB_USERNAME', '3976251_lockme');
define('DB_PASSWORD', 'q95lNw:M7y#*lY88j34kj5h');
define('DB_NAME', '3976251_lockme');
 
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>