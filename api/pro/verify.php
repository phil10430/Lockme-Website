<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$headers = getallheaders();
$auth    = $headers['Authorization'] ?? '';
if ($auth !== 'Bearer ' . APP_SECRET_KEY) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$code = strtoupper(trim($data['code'] ?? ''));
    
if (!$code) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing code']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT used, expires_at
        FROM pro_codes
        WHERE code = :code AND used = 1
    ");
    $stmt->execute(['code' => $code]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['active' => false]);
        exit;
    }

    $active = strtotime($row['expires_at']) > time();

    http_response_code(200);
    echo json_encode([
        'active'     => $active,
        'expires_at' => $row['expires_at']
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
}