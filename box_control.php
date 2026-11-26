<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/helper_functions.php';
require_once __DIR__ . '/includes/box_access.php';

$boxControlError = "";
$wishedAction = "";

// Berechtigung serverseitig bestimmen (Besitzer ODER Remote Controller)
$ctx = getBoxContext($pdo, $_SESSION["username"]);
if (!$ctx) { http_response_code(403); exit; }

$state       = $ctx['state'];                       // Besitzer-Zeile (bei Remote) bzw. eigene Zeile
$boxName     = $ctx['boxName'];
$appLoggedIn = $state['app_logged_in'];
$appActive   = $state['app_active'];
$lockStatus  = $ctx['boxRow']['lock_status'] ?? 0;
$targetUserId = (int)$state['id'];                  // dessen wished_action wird beschrieben

$allowed = ($boxName != 0
         && $appLoggedIn == 1
         && $appActive == 1
         && $state['pro_version'] == "1");

if ($allowed)
{
   
    if ($_SERVER["REQUEST_METHOD"] == "POST") {  // Processing form data when form is submitted

        $protectionLevelTimer = "0";
        $protectionLevelPassword = "0";
        $openTimeUnix = PLACEHOLDER; // initialize as placeholder
        $openTime = test_input($_POST['openTime']);
        $password = test_input($_POST['password']);  // kommt aus #password
        $openTimeRandom = test_input($_POST['openTimeRandom']);

        // Password Check
        if (empty($password))
        {
            $password = PLACEHOLDER;
        } elseif (!isValidPassword($password)){
            $boxControlError = "Invalid password! Password must only contain a-z A-Z 0-9 and have 1 to 10 characters.";
        }
      
        $closeBoxIntent = false;

        if ( $lockStatus==0) 
        {
            if (isset($_POST['closeBoxWithTimer'])) {
                if (validateDate($openTime)) 
                {
                        $dt = DateTime::createFromFormat("d/m/Y H:i", $openTime, new DateTimeZone('Europe/Berlin'));
                        $openTimeUnix = $dt->getTimestamp();
                        $protectionLevelTimer = "1";
                        $closeBoxIntent = true;
                } else {
                        $boxControlError = "Invalid date";
                }
            }
                               
            
            if (isset($_POST['closeBoxWithPw'])) 
            {
                    $protectionLevelPassword = "1";
                    $closeBoxIntent = true;
            }

            
            if (isset($_POST['closeBoxWithPasswordTimer'])) 
            {

                if (validateDate($openTime)) 
                {
                        $dt = DateTime::createFromFormat("d/m/Y H:i", $openTime, new DateTimeZone('Europe/Berlin'));
                        $openTimeUnix = $dt->getTimestamp();
                        $protectionLevelTimer = "1";
                        $closeBoxIntent = true;
                } else {
                        $boxControlError = "Invalid date";
                }

                $protectionLevelPassword = "1";
             
            }

            if (isset($_POST['closeBoxWithRandomTimer'])) 
            {
                $openTimeUnix   = intval($openTimeRandom);
                $protectionLevelTimer = "1";
                $closeBoxIntent = true;
            }

            if ( empty($boxControlError) && $closeBoxIntent ){
                $wishedAction = MSG_CLOSE . MSG_SEPARATOR .
                    $protectionLevelTimer . MSG_SEPARATOR . $protectionLevelPassword . MSG_SEPARATOR .
                    $openTimeUnix . MSG_SEPARATOR .
                    $password ;
            }

            
         
        }
        else
        {
            if (isset($_POST['openBox'])) 
            {
                $wishedAction = MSG_OPEN . MSG_SEPARATOR .
                PLACEHOLDER ;
            }     
            if (isset($_POST['openBoxWithPw'])) 
            {    
              $wishedAction = MSG_OPEN . MSG_SEPARATOR . $password ;     
            }      

            if (isset($_POST['extendTime'])) {
         
                   
                if (validateDate($openTime)) 
                {
                  
                        $dt = DateTime::createFromFormat("d/m/Y H:i", $openTime, new DateTimeZone('Europe/Berlin'));
                        $openTimeUnix = $dt->getTimestamp();
                    
                        $protectionLevelTimer = "1";
                        $wishedAction = EXTEND_OPEN_TIME . MSG_SEPARATOR .
                            $protectionLevelTimer . MSG_SEPARATOR .
                            $openTimeUnix . MSG_SEPARATOR .
                            $password ;


                } else {
                        $boxControlError = "Invalid date";
                }
            }
        } 
        
         // Keyholder nur vormerken. Gesetzt wird er, wenn die Box wirklich verschlossen meldet.
        if ($closeBoxIntent && empty($boxControlError) && $wishedAction !== '') {
            $stmt = $pdo->prepare("INSERT INTO box_details (box_id, keyholder, box_content, pending_keyholder, pending_keyholder_user_id, pending_keyholder_at)
                                   VALUES (?, '', NULL, ?, ?, NOW())
                                   ON DUPLICATE KEY UPDATE pending_keyholder = VALUES(pending_keyholder),
                                                           pending_keyholder_user_id = VALUES(pending_keyholder_user_id),
                                                           pending_keyholder_at = VALUES(pending_keyholder_at)");
            $stmt->execute([$boxName, $_SESSION['username'], (int) $_SESSION['id']]);
        }
     
        $sql = "UPDATE users SET wished_action = :wished_action WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':wished_action' => $wishedAction,
            ':id'            => $targetUserId
        ]);

    }
}
if (!empty($boxControlError)) {
    echo '<div class="alert alert-danger">' . $boxControlError . '</div>';
}
 
?>