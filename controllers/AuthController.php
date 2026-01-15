<?php
require_once '../models/User.php';

class AuthController {
    private $userModel;

    public function __construct($conn) {
        $this->userModel = new User($conn);
    }

    public function login($email, $password) {
        $user = $this->userModel->authenticate($email, $password);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            return ['success' => true, 'redirect' => ($user['role'] === 'admin' ? "admin-dashboard.php" : "user-dashboard.php")];
        } else {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: ../public/login.php");
        exit();
    }

    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public static function isUser() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
    }
}
?>
