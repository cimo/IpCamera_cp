<?php
require_once(dirname(dirname(__DIR__)) . "/Config.php");

class Database {
    // Vars
    private $config;
    
    private $pdo;
    
    // Properties
    public function getPdo() {
        return $this->pdo;
    }
    
    // Functions public
    public function __construct() {
        $this->config = new Config();
        
        set_exception_handler(function($error) {
            error_log($error->getMessage());
            
            exit('Pdo problem!');
        });

        $connectionFields = $this->config->getDatabaseConnectionFields();
        
        if ($connectionFields[0] != "" && $connectionFields[1] != "" && $connectionFields[2] != "" && $connectionFields[3] != "")
            $this->pdo = new PDO($connectionFields[0], $connectionFields[1], $connectionFields[2],$connectionFields[3]);
    }
    
    public function close() {
        unset($this->pdo);
    }

    // Functions private
}