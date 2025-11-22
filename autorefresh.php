<?php

// get lock/con-status from database and send it back to ajax script
require_once __DIR__ . '/includes/config.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
$username = trim($_POST['username']);
$query = "SELECT * FROM users WHERE username = :username";
$stmt = $pdo->prepare($query);
$stmt->execute([':username' => $username]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$jsonOutput = [   
    'lockStatus'        => $row['lock_status'],
    'conStatus'         => $row['con_status'],
    'appLoggedIn'       => $row['app_logged_in'],
    'appActive'         => $row['app_active'],
    'openTime'          => $row['open_time'],
    'timeLeft'          => $row['time_left'],
    'lockedSince'       => $row['locked_since']
];

echo json_encode($jsonOutput);

}
?>
