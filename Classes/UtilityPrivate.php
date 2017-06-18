<?php
require_once("Utility.php");
require_once("Query.php");

class UtilityPrivate {
    // Vars
    private $utility;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct() {
        $this->utility = new Utility();
        $this->query = new Query($this->utility->getDatabase());
    }
    
    public function checkSessionOverTime() {
        if (isset($_SESSION['user_activity']) == false)
            $_SESSION['user_activity'] = "";
        
        $sessionMaxIdleTime = 3600;
        
        if ($sessionMaxIdleTime > 0) {
            if (isset($_SESSION['user_last_activity_time']) == true) {
                $timeLapse = time() - $_SESSION['user_last_activity_time'];
                
                if ($timeLapse > $sessionMaxIdleTime)
                    $_SESSION['user_activity'] = "Session time is over, please refresh the page.";
            }
            
            $_SESSION['user_last_activity_time'] = time();
        }
    }
    
    // Functions private
}