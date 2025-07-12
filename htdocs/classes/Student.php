<?php
require_once __DIR__ . '/../includes/Database.php';

class Student {
    private $conn;
    private $table = 'student';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function register($studentNo, $studentName, $phoneNo, $email, $password) {
        // Check if student number or email already exists
        $query = "SELECT * FROM {$this->table} WHERE StudentNo = :studentNo OR Email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['studentNo' => $studentNo, 'email' => $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Student No or Email already exists.'];
        }
        
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $query = "INSERT INTO {$this->table} (StudentNo, StudentName, PhoneNo, Email, PasswordHash) VALUES (:studentNo, :studentName, :phoneNo, :email, :passwordHash)";
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([
            'studentNo' => $studentNo,
            'studentName' => $studentName,
            'phoneNo' => $phoneNo,
            'email' => $email,
            'passwordHash' => $passwordHash
        ]);
        if ($result) {
            return ['success' => true, 'message' => 'Registration successful.'];
        } else {
            return ['success' => false, 'message' => 'Registration failed.'];
        }
    }

    public function login($studentNo, $password) {
        $query = "SELECT * FROM {$this->table} WHERE StudentNo = :studentNo LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['studentNo' => $studentNo]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['PasswordHash'])) {
            return ['success' => true, 'user' => $user];
        } else {
            return ['success' => false, 'message' => 'Invalid Student No or Password.'];
        }
    }
} 