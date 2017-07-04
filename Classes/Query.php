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
    
    public function selectAllDevicesDatabase() {
        $query = $this->database->getPdo()->prepare("SELECT * FROM devices");
        
        $query->execute();
        
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function selectCameraDatabase($cameraNumber) {
        $query = $this->database->getPdo()->prepare("SELECT * FROM cameras
                                                        WHERE camera_number = :cameraNumber");
        
        $query->bindValue(":cameraNumber", $cameraNumber);
        
        $query->execute();
        
        return $query->fetch(PDO::FETCH_ASSOC);
    }
    
    public function selectAllCamerasDatabase() {
        $query = $this->database->getPdo()->prepare("SELECT * FROM cameras");
        
        $query->execute();
        
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Functions private
}