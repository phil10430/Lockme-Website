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
        SELECT * FROM pro_codes
        WHERE code = :code
        AND (
            used = 0
            OR (used = 1 AND expires_at > NOW())
        )
    ");
    $stmt->execute(['code' => $code]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(['error' => 'Invalid or expired code']);
        exit;
    }

    // Nur updaten wenn noch nicht eingelöst
    if ($row['used'] == 0) {
        $expires_at = date('Y-m-d H:i:s', strtotime('+' . $row['duration'] . ' days'));

        $stmt = $pdo->prepare("
            UPDATE pro_codes
            SET
                used        = 1,
                redeemed_at = NOW(),
                expires_at  = :expires_at
            WHERE code = :code
        ");
        $stmt->execute([
            'expires_at' => $expires_at,
            'code'       => $code
        ]);
    } else {
        // Bereits eingelöst → expires_at aus DB nehmen
        $expires_at = $row['expires_at'];
    }

    http_response_code(200);
    echo json_encode([
        'success'    => true,
        'expires_at' => $expires_at
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
}