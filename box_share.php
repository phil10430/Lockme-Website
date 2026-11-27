<?php
require_once __DIR__ . '/includes/config.php';

$boxId = trim((string) ($_GET['box_id'] ?? ''));
$target = 'community.php';
if (preg_match('/^\d+$/D', $boxId)) {
    $target .= '?box=' . rawurlencode($boxId);
}
header('Location: /' . $target, true, 301);
exit;