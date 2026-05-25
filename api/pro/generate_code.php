<?php
// api/pro/generate-code.php
// Wird NUR von WooCommerce aufgerufen

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$headers = getallheaders();
$auth    = $headers['Authorization'] ?? '';
if ($auth !== 'Bearer o06194lEmvJBKWLPlV5zYMyIkkZXgC') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$code     = strtoupper(trim($data['code']     ?? ''));
$order_id = $data['order_id'] ?? null;
$duration = $data['duration'] ?? 365;
$email    = $data['email']    ?? null;

if (!$code || !$order_id || !$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

$pdo  = new PDO('mysql:host=localhost;dbname=deine_db', 'user', 'password');
$stmt = $pdo->prepare("
    INSERT INTO pro_codes (code, order_id, duration, email, created_at)
    VALUES (:code, :order_id, :duration, :email, NOW())
");
$stmt->execute([
    'code'     => $code,
    'order_id' => $order_id,
    'duration' => $duration,
    'email'    => $email
]);

http_response_code(200);
echo json_encode(['success' => true]);