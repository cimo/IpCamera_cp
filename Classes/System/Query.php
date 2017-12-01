<?php
class Query {
    // Vars
    private $database;
    
    // Properties
    
    // Functions public
    public function __construct($database) {
        $this->database = $database;
    }
    
    public function selectSettingDatabase() {
        $query = $this->database->getPdo()->prepare("SELECT * FROM settings
                                                        WHERE id = :id");
        
        $query->bindValue(":id", 1);
        
        $query->execute();
        
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    
    public function selectDeviceDatabase($id) {
        $query = $this->database->getPdo()->prepare("SELECT * FROM devices
                                                        WHERE id = :id");
        
        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    
    public function selectAllDeviceDatabase() {
        $query = $this->database->getPdo()->prepare("SELECT * FROM devices");
        
        $query->execute();
        
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function selectApparatusDatabase($number) {
        $query = $this->database->getPdo()->prepare("SELECT * FROM apparatus
                                                        WHERE number = :number");
        
        $query->bindValue(":number", $number);
        
        $query->execute();
        
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    
    public function selectAllApparatusDatabase() {
        $query = $this->database->getPdo()->prepare("SELECT * FROM apparatus");
        
        $query->execute();
        
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function selectUserDatabase($value) {
        if (is_numeric($value) == true) {
            $query = $this->database->getPdo()->prepare("SELECT * FROM users
                                                            WHERE id = :id");
            
            $query->bindValue(":id", $value);
        }
        else if (filter_var($value, FILTER_VALIDATE_EMAIL) !== false) {
            $query = $this->database->getPdo()->prepare("SELECT * FROM users
                                                            WHERE email = :email");
            
            $query->bindValue(":email", $value);
        }
        else {
            $query = $this->database->getPdo()->prepare("SELECT * FROM users
                                                            WHERE username = :username");
            
            $query->bindValue(":username", $value);
        }
        
        $query->execute();
        
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    
    public function selectAllUserDatabase($idExclude = 0) {
        $query = $this->database->getPdo()->prepare("SELECT * FROM users
                                                        WHERE id != :idExclude");
        
        $query->bindValue(":idExclude", $idExclude);
        
        $query->execute();
        
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function selectUserWithHelpCodeDatabase($helpCode) {
        $query = $this->database->getPdo()->prepare("SELECT * FROM users
                                                        WHERE help_code IS NOT NULL
                                                        AND help_code = :helpCode");
        
        $query->bindValue(":helpCode", $helpCode);
        
        $query->execute();
        
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    
    public function selectRoleUserDatabase($roleId, $modify = false) {
        $roleIdExplode = explode(",", $roleId);
        array_pop($roleIdExplode);
        
        $level = Array();
        
        foreach($roleIdExplode as $key => $value) {
            $query = $this->database->getPdo()->prepare("SELECT level FROM roles_users
                                                            WHERE id = :value");
            
            $query->bindValue(":value", $value);
            
            $query->execute();
            
            $row = $query->fetch(PDO::FETCH_ASSOC);
            
            if ($modify == true)
                array_push($level, ucfirst(strtolower(str_replace("ROLE_", "", $row['level']))));
            else
                array_push($level, $row['level']);
        }
        
        return $level;
    }
    
    public function selectAllRoleUserDatabase($change = false) {
        $query = $this->database->getPdo()->prepare("SELECT * FROM roles_users");
        
        $query->execute();
        
        $rows = $query->fetchAll(PDO::FETCH_ASSOC);
        
        if ($change == true) {
            foreach ($rows as &$value) {
                $value = str_replace("ROLE_", "", $value);
                $value = array_map("strtolower", $value);
                $value = array_map("ucfirst", $value);
            }
        }
        
        return $rows;
    }
    
    // Functions private
}