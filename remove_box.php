<?php
session_start();

if (!isset($_SESSION["id"])) {
    die("Nicht eingeloggt.");
}

$userId = $_SESSION["id"];
$boxId  = test_input($_POST["boxId"]);

if (!preg_match('/^\d{6}$/', $boxId)) {
    die("Ungültige Box-ID.");
}

try {
    $sql = "DELETE FROM user_boxes WHERE user_id = :user_id AND box_id = :box_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $userId,
        ':box_id'  => $boxId,
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Box entfernt.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Box nicht gefunden.']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ein Fehler ist aufgetreten.']);
}