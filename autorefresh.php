<?php

// get lock/con-status from database and send it back to ajax script
require_once __DIR__ . '/includes/config.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){

// Query 1: users Tabelle
$username = trim($_POST['username']);
$query = "SELECT * FROM users WHERE username = :username";
$stmt = $pdo->prepare($query);
$stmt->execute([':username' => $username]);
$userRow = $stmt->fetch(PDO::FETCH_ASSOC);

$boxName = $userRow['box_name_con'];

// Query 2: box_data_actual Tabelle
$query2 = "SELECT * FROM box_data_actual WHERE box_name = :box_name";
$stmt2 = $pdo->prepare($query2);
$stmt2->execute([':box_name' => $boxName]);
$boxRow = $stmt2->fetch(PDO::FETCH_ASSOC);



$jsonOutput = [
    // aus users
    'boxName'           => $userRow['box_name_con'],   
    'appLoggedIn'       => $userRow['app_logged_in'],
    'appActive'         => $userRow['app_active'],

    // aus box_data_actual
    'lockStatus'        => $boxRow['lock_status'],
    'openTime'          => $boxRow['open_time'],
    'timeLeft'          => $boxRow['time_left'],
    'lockedSince'       => $boxRow['locked_since']
];

echo json_encode($jsonOutput);

}
?>
