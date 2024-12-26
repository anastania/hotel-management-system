<?php

class Auth {
    private static $instance = null;
    private $user = null;
    
    private function __construct() {
        if (isset($_SESSION['user_id'])) {
            $this->user = User::find($_SESSION['user_id']);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function attempt($email, $password) {
        if (!Security::rateLimitCheck('login_' . $email)) {
            throw new Exception('Too many login attempts. Please try again later.');
        }
        
        $user = User::findBy('email', $email);
        
        if ($user && $user->verifyPassword($password)) {
            $this->login($user);
            return true;
        }
        
        return false;
    }
    
    public function login(User $user) {
        $_SESSION['user_id'] = $user->id;
        $this->user = $user;
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Log the login
        Logger::info('User logged in', ['user_id' => $user->id]);
    }
    
    public function logout() {
        if ($this->user) {
            Logger::info('User logged out', ['user_id' => $this->user->id]);
        }
        
        $this->user = null;
        session_unset();
        session_destroy();
        
        // Start a new session
        session_start();
        session_regenerate_id(true);
    }
    
    public function check() {
        return $this->user !== null;
    }
    
    public function user() {
        return $this->user;
    }
    
    public function id() {
        return $this->user ? $this->user->id : null;
    }
    
    public function requireLogin() {
        if (!$this->check()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
    }
    
    public function requireAdmin() {
        $this->requireLogin();
        
        if (!$this->user->isAdmin()) {
            Logger::warning('Unauthorized admin access attempt', [
                'user_id' => $this->user->id,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            header('HTTP/1.1 403 Forbidden');
            include __DIR__ . '/../templates/error.php';
            exit;
        }
    }
    
    public function requireVerified() {
        $this->requireLogin();
        
        if (!$this->user->email_verified) {
            header('Location: ' . BASE_URL . '/verify_email.php');
            exit;
        }
    }
}
