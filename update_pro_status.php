<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit;
}

$username = trim($_POST["username"] ?? '');
$purchaseToken = trim($_POST["purchaseToken"] ?? '');
$subscriptionPlatform = trim($_POST["subscriptionPlatform"] ?? '');
$productId = trim($_POST["productId"] ?? '');

if (empty($username)) {
    echo "username missing";
    exit;
}

$proVersion = !empty($purchaseToken) ? 1 : 0;

try {
    $stmt = $pdo->prepare("
        UPDATE users
        SET pro_version = :pro_version,
            pro_version_purchase_token = :token,
            pro_version_subscription_platform = :platform,
            pro_version_product_id = :product
        WHERE username = :username
    ");
    $stmt->execute([
        ':username' => $username,
        ':pro_version' => $proVersion,
        ':token' => $purchaseToken,
        ':platform' => $subscriptionPlatform,
        ':product' => $productId
    ]);
    echo "pro_status_updated";
} catch (PDOException $e) {
    echo "Database error. Please try again later.";
}