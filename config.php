<?php
/* stim-me.de */
define('DB_SERVER', 'localhost:3306');
define('DB_USERNAME', 'web688s4_lockme');
define('DB_PASSWORD', 'cgaVduKyYHQ04BKfsJadfaaV8QRI40kkal');
define('DB_NAME', 'web688s4_lockme');
 

/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>