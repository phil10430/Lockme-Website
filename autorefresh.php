<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/box_access.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_SESSION['username'])) {

    $ctx = getBoxContext($pdo, $_SESSION['username']);
    if (!$ctx) { http_response_code(403); exit; }

    $state = $ctx['state'];
    $box   = $ctx['boxRow'];

    echo json_encode([
        'isRemote'      => $ctx['isRemote'],
        'proVersion'    => $state['pro_version'],
        'boxName'       => $ctx['boxName'],
        'appLoggedIn'   => $state['app_logged_in'],
        'appActive'     => $state['app_active'],

        'lockStatus'              => $box['lock_status']               ?? 0,
        'protectionLevelPassword' => $box['protection_level_password'] ?? 0,
        'protectionLevelTimer'    => $box['protection_level_timer']    ?? 0,
        'openTime'                => !empty($box['open_time'])
                                        ? date("Y-m-d H:i", strtotime($box['open_time'])) : '',
        'timeLeft'                => $box['time_left']    ?? '',
        'lockedSince'             => $box['locked_since'] ?? '',
    ]);
} else {
    http_response_code(403);
}