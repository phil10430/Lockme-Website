<?php
// api/pro/redeem.php
// Wird NUR von der App aufgerufen — NICHT von der Website

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$headers = getallheaders();
$auth    = $headers['Authorization'] ?? '';
if ($auth !== 'Bearer zVLwe26JrSrMc7pmsrfmozROBTU4ae') {
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

$pdo  = new PDO('mysql:host=localhost;dbname=deine_db', 'user', 'password');
$stmt = $pdo->prepare("SELECT * FROM pro_codes WHERE code = :code AND used = 0");
$stmt->execute(['code' => $code]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'Invalid or already used code']);
    exit;
}

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

http_response_code(200);
echo json_encode([
    'success'    => true,
    'expires_at' => $expires_at
]);