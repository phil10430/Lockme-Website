<?php
/** Access and box selection for Control Center. */
function remoteControlContext(PDO $pdo, int $viewerId, ?int $requestedBoxId): array
{
    $stmt = $pdo->prepare('SELECT id, username, pro_version, app_logged_in, app_active, box_id_con FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$viewerId]);
    $viewer = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$viewer) throw new RuntimeException('User not found.');

    // Only connected Pro owners can provide remote access.
    $stmt = $pdo->prepare('SELECT bp.box_id, owner.id AS owner_id, owner.username AS owner_username, owner.pro_version, owner.app_logged_in, owner.app_active, owner.box_id_con FROM box_permissions bp INNER JOIN user_boxes ub ON ub.box_id = bp.box_id INNER JOIN users owner ON owner.id = ub.user_id WHERE bp.user_id = ? AND bp.revoked_at IS NULL AND owner.pro_version = 1 AND owner.app_logged_in = 1 AND owner.app_active = 1 AND owner.box_id_con = bp.box_id ORDER BY bp.box_id');
    $stmt->execute([$viewerId]);
    $sharedBoxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $hasPro = (int)$viewer['pro_version'] === 1;
    $allowed = $hasPro || count($sharedBoxes) > 0;
    $box = null;
    $isShared = false;
    if ($requestedBoxId !== null) {
        foreach ($sharedBoxes as $candidate) {
            if ((int)$candidate['box_id'] === $requestedBoxId) {
                $box = $candidate; $isShared = true; break;
            }
        }
        if ($box === null && $hasPro && (int)$viewer['box_id_con'] === $requestedBoxId && $requestedBoxId > 0) {
            $box = ['box_id' => $requestedBoxId, 'owner_id' => $viewerId, 'owner_username' => $viewer['username'], 'pro_version' => $viewer['pro_version'], 'app_logged_in' => $viewer['app_logged_in'], 'app_active' => $viewer['app_active'], 'box_id_con' => $viewer['box_id_con']];
        }
    } elseif ($hasPro && (int)$viewer['box_id_con'] > 0) {
        $box = ['box_id' => (int)$viewer['box_id_con'], 'owner_id' => $viewerId, 'owner_username' => $viewer['username'], 'pro_version' => $viewer['pro_version'], 'app_logged_in' => $viewer['app_logged_in'], 'app_active' => $viewer['app_active'], 'box_id_con' => $viewer['box_id_con']];
    } elseif ($sharedBoxes) {
        $box = $sharedBoxes[0]; $isShared = true;
    }
    return compact('viewer', 'sharedBoxes', 'hasPro', 'allowed', 'box', 'isShared');
}
