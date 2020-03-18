<?php
namespace App\Classes\System;

class QueryCustom {
    // Vars
    private $helper;
    private $query;
    
    private $connection;
    
    // Properties
      
    // Functions public
    public function __construct($helper) {
        $this->helper = $helper;
        $this->query = $this->helper->getQuery();
        
        $this->connection = $this->helper->getConnection();
    }
    
    public function selectIpCameraDatabase($type, $id, $password) {
        if ($type == "aes") {
            $settingRow = $this->query->selectSettingDatabase();
            
            $query = $this->connection->prepare("SELECT AES_DECRYPT(:password, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512))) AS password
                                                        FROM ipCamera_device
                                                    WHERE id = :id
                                                    ORDER by name ASC");
            
            $query->bindValue(":password", $password);
            $query->bindValue(":id", $id);
            
            $query->execute();
            
            return $query->fetch();
        }
        
        return false;
    }
    
    public function selectAllIpCameraDatabase() {
        $query = $this->connection->prepare("SELECT * FROM ipCamera_device
                                                ORDER by name ASC");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function updateIpCameraDatabase($type, $id, $columnName, $value) {
        if ($type == "aes") {
            if ($value != null) {
                $settingRow = $this->query->selectSettingDatabase();

                $query = $this->connection->prepare("UPDATE IGNORE ipCamera_device
                                                            SET {$columnName} = AES_ENCRYPT(:{$columnName}, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512)))
                                                        WHERE id = :id");

                $query->bindValue(":{$columnName}", $value);
                $query->bindValue(":id", $id);

                return $query->execute();
            }
        }
        
        return false;
    }
    
    public function deleteIpCameraDatabase($type, $id = 0) {
        if ($type == "one") {
            $query = $this->connection->prepare("DELETE FROM ipCamera_device
                                                    WHERE id = :id");
            
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "all") {
            $query = $this->connection->prepare("DELETE FROM ipCamera_device");
            
            return $query->execute();
        }
    }
    
    // Functions private
}