<?php
// /api/register.php - FÜR MOBILE APP
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

error_reporting(E_ALL);
ini_set('display_errors', 0);

ob_start();

$response = ['success' => false, 'message' => '', 'errors' => []];

try {
    require_once __DIR__ . '/../includes/config.php';
    require_once __DIR__ . '/../includes/helper_functions.php';
    
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        $response['message'] = "Method not allowed";
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // Input auslesen (JSON oder Form)
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');
    $confirm_password = trim($input['confirm_password'] ?? '');
    $accept_terms = isset($input['accept_terms']) && !empty($input['accept_terms']);
    
    $errors = [];
    
    // 🔹 USERNAME VALIDIERUNG
    if (empty($username)) {
        $errors['username'] = "Please enter a username.";
    } elseif (strlen($username) < 3) {
        $errors['username'] = "Username must be at least 3 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,}$/', $username)) {
        $errors['username'] = "Username can only contain letters, numbers and underscores.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?)");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors['username'] = "This username is already taken.";
            }
        } catch (PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            $errors['database'] = "Database error checking username.";
        }
    }
    
    // 🔹 EMAIL VALIDIERUNG
    if (empty($email)) {
        $errors['email'] = "Please enter an email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors['email'] = "This email is already registered.";
            }
        } catch (PDOException $e) {
            error_log("DB Error: " . $e->getMessage());
            $errors['database'] = "Database error checking email.";
        }
    }
    
    // 🔹 PASSWORD VALIDIERUNG
    if (empty($password)) {
        $errors['password'] = "Please enter a password.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }
    
    // 🔹 TERMS CHECK
    if (!$accept_terms) {
        $errors['terms'] = "Please accept the Terms of Service.";
    }
    
    // ❌ FEHLER ZURÜCKGEBEN
    if (!empty($errors)) {
        $response['errors'] = $errors;
        $response['message'] = "Please check your input.";
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
    // ✅ USER ERSTELLEN
    try {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $token = bin2hex(random_bytes(32));
        
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password, email_verified, verification_token, created_at)
            VALUES (?, ?, ?, 0, ?, NOW())
        ");
        
        if ($stmt->execute([$username, $email, $hash, $token])) {
            // 📧 E-MAIL VERSENDEN
            $verify_link = "https://lockmebox.com/verify_email.php?token=" . urlencode($token);
            $username_safe = htmlspecialchars($username);
            
            ob_end_clean();
            ob_start();
            
            include __DIR__ . "/../templates/email_verify.php";
            $message = ob_get_clean();
            
            $subject = "Confirm your registration";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: noreply@lockmebox.com\r\n";
            
            @mail($email, $subject, $message, $headers);
            
            // ✅ SUCCESS RESPONSE
            $response['success'] = true;
            $response['message'] = "Registration successful! Please check your email to verify your account.";
            
            ob_start();
            echo json_encode($response);
            ob_end_clean();
            echo json_encode($response);
            exit;
        } else {
            throw new Exception("Could not create user");
        }
        
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        $response['message'] = "Registration failed. Please try again later.";
        ob_end_clean();
        echo json_encode($response);
        exit;
    }
    
} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    $response['message'] = "Server error. Please try again.";
    ob_end_clean();
    echo json_encode($response);
    exit;
}
?>