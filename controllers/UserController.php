<?php
require_once '../models/User.php';

class UserController {
    private $userModel;

    public function __construct($conn) {
        $this->userModel = new User($conn);
    }

    public function createUser($name, $email, $password) {
        return $this->userModel->create($name, $email, $password);
    }

    public function deleteUser($id) {
        return $this->userModel->delete($id);
    }

    public function getAllUsers() {
        return $this->userModel->getAllUsers();
    }

    public function getByEmail($email) {
        return $this->userModel->findByEmail($email);
    }
}
?>
