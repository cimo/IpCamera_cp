<?php
namespace App;

class Config {
    // Vars
    private $databaseConnectionFields;
    private $protocol;
    private $pathRoot;
    private $urlRoot;
    private $supportSymlink;
    private $file;
    private $name;
    
    // Properties
    public function getDatabaseConnectionFields() {
        return $this->databaseConnectionFields;
    }
    
    public function getProtocol() {
        return $this->protocol;
    }
    
    public function getPathRoot() {
        return $this->pathRoot;
    }
    
    public function getUrlRoot() {
        return $this->urlRoot;
    }
    
    public function getSupportSymlink() {
        return $this->supportSymlink;
    }
    
    public function getFile() {
        return $this->file;
    }
    
    public function getName() {
        return $this->name;
    }
    
    // Functions public
    public function __construct() {
        $this->databaseConnectionFields = Array("", "", "", Array());
        $this->protocol = isset($_SERVER['HTTPS']) == true ? "https://" : "http://";
        $this->pathRoot = "/projects/ipcamera_cp/root/symfony_fw/";
        $this->urlRoot = "/projects/ipcamera_cp/root/symfony_fw/public";
        $this->supportSymlink = true;
        $this->file = "/index.php";
        $this->name = "IpCamera cp 1.0.0";
    }
    
    // Functions private
}