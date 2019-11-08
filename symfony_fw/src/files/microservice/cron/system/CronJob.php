<?php
namespace App\files\microservice\cron\system;

require_once(dirname(dirname(dirname(dirname(__DIR__)))) . "/Config.php");

Class CronJob {
    // Vars
    
    // Properties
    
    // Functions public
    public function __construct() {
        $id = intval($_SERVER['argv'][1]);
        
        if ($id > 0) {
            $config = new \App\Config();

            $databaseConnectionFields = $config->getDatabaseConnectionFields();

            if ($databaseConnectionFields[0] != "" && $databaseConnectionFields[1] != "" && $databaseConnectionFields[2] != "") {
                $pdo = new \PDO($databaseConnectionFields[0], $databaseConnectionFields[1], $databaseConnectionFields[2], $databaseConnectionFields[3]);

                $query = $pdo->prepare("UPDATE microservice_cron
                                            SET last_execution = :last_execution
                                            WHERE id = :id");

                $query->bindValue(":last_execution", date("Y-m-d H:i:s"));
                $query->bindValue(":id", $id);

                $query->execute();
            }
        }
    }
    
    // Functions private
}

$job = new CronJob();