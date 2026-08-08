<?php

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $role;
    public $name;
    public $email;
    public $password;
    public $phone;
    public $created_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, email, password_hash, phone, role)
                  VALUES (:name, :email, :password, :phone, :role)
                  RETURNING id";
        
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->role = 'customer'; 

        $algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        $password_hash = password_hash($this->password, $algorithm);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":role", $this->role);

        if($stmt->execute()) {
            $this->created_id = (int) $stmt->fetchColumn();
            return true;
        }
        return false;
    }

    public function emailExists() {
        $query = "SELECT id, name, password_hash, role, phone 
                  FROM " . $this->table_name . " 
                  WHERE email = ? 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);

        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->password = $row['password_hash']; 
            $this->role = $row['role'];
            $this->phone = $row['phone'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name, phone=:phone
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function refreshPasswordHash(string $plainPassword): void {
        $algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        if (!password_needs_rehash($this->password, $algorithm)) return;
        $stmt = $this->conn->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        $stmt->execute([
            ':password_hash' => password_hash($plainPassword, $algorithm),
            ':id' => $this->id
        ]);
    }
}
?>
