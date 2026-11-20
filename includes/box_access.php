<?php
/**
 * Ermittelt, welche Box der User sieht und von wessen Status.
 * Wird von index.php UND autorefresh.php (und den Steuer-Endpunkten) genutzt.
 */
function getBoxContext(PDO $pdo, string $username): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $me = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$me) return null;

    $ctx = [
        'me'       => $me,
        'isRemote' => false,
        'state'    => $me,                    // Zeile für app_logged_in / app_active / pro_version
        'boxName'  => (int)$me['box_id_con'],
        'boxRow'   => [],
    ];

    // Remote-Prüfung, wenn eigene Box fehlt ODER User selbst kein Pro hat
    if ($ctx['boxName'] <= 0 || $me['pro_version'] != "1") {
        $stmt = $pdo->prepare(
            "SELECT box_id, granted_by
               FROM box_permissions
              WHERE user_id = :uid
                AND (revoked_at IS NULL OR revoked_at > NOW())
              ORDER BY created_at DESC
              LIMIT 1"
        );
        $stmt->execute([':uid' => $me['id']]);
        $perm = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($perm) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $perm['granted_by']]);
            $owner = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($owner) {
                $ctx['isRemote'] = true;
                $ctx['state']    = $owner;
                $ctx['boxName']  = ((int)$owner['box_id_con'] === (int)$perm['box_id'])
                                    ? (int)$perm['box_id'] : 0;
            }
        }
    }

    if ($ctx['boxName'] > 0) {
        $stmt = $pdo->prepare("SELECT * FROM box_data_actual WHERE box_id = :box_id");
        $stmt->execute([':box_id' => $ctx['boxName']]);
        $ctx['boxRow'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    return $ctx;
}