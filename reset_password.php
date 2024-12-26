<?php
require_once 'config.php';
require_once 'includes/Security.php';
require_once 'includes/Database.php';
require_once 'includes/Logger.php';

Security::init();

$error = '';
$success = '';
$db = Database::getInstance();

if (isset($_GET['token'])) {
    $token = Security::sanitizeInput($_GET['token']);
    
    // Verify token
    $sql = "SELECT * FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND used = 0";
    $reset = $db->fetch($sql, [$token]);
    
    if (!$reset) {
        $error = 'Invalid or expired password reset token.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::validateCSRF();
    
    if (isset($_POST['email'])) {
        // Request password reset
        $email = Security::sanitizeInput($_POST['email']);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } else {
            // Check if email exists
            $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
            
            if ($user) {
                $token = Security::generateRandomToken();
                
                // Store token
                $db->insert('password_resets', [
                    'user_id' => $user['id'],
                    'token' => $token,
                    'created_at' => date('Y-m-d H:i:s'),
                    'used' => 0
                ]);
                
                // Send reset email
                if (Mailer::sendPasswordReset($email, $token)) {
                    $success = 'Password reset instructions have been sent to your email.';
                } else {
                    $error = 'Failed to send password reset email. Please try again later.';
                }
            } else {
                // Don't reveal if email exists
                $success = 'If your email exists in our system, you will receive password reset instructions.';
            }
        }
    } elseif (isset($_POST['password'], $_POST['token'])) {
        // Reset password
        $password = $_POST['password'];
        $token = Security::sanitizeInput($_POST['token']);
        
        if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } else {
            $reset = $db->fetch("SELECT * FROM password_resets WHERE token = ? AND used = 0", [$token]);
            
            if ($reset) {
                $hashedPassword = Security::hashPassword($password);
                
                try {
                    $db->beginTransaction();
                    
                    // Update password
                    $db->update('users', 
                        ['password' => $hashedPassword],
                        'id = ?',
                        [$reset['user_id']]
                    );
                    
                    // Mark token as used
                    $db->update('password_resets',
                        ['used' => 1],
                        'token = ?',
                        [$token]
                    );
                    
                    $db->commit();
                    $success = 'Your password has been successfully reset. You can now login with your new password.';
                    
                } catch (Exception $e) {
                    $db->rollback();
                    Logger::error('Password reset failed: ' . $e->getMessage());
                    $error = 'Failed to reset password. Please try again later.';
                }
            } else {
                $error = 'Invalid or expired password reset token.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['token'])): ?>
            <!-- Reset Password Form -->
            <form method="POST" action="reset_password.php" class="form">
                <h2>Reset Password</h2>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
        <?php else: ?>
            <!-- Request Reset Form -->
            <form method="POST" action="reset_password.php" class="form">
                <h2>Request Password Reset</h2>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Request Reset</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
