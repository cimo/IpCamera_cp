<?php
require_once("Utility.php");
require_once("UtilityPrivate.php");

class Root {
    // Vars
    private $utility;
    
    // Properties
    
    // Functions public
    public function __construct() {
        $this->utility = new Utility();
        $this->utilityPrivate = new UtilityPrivate();
        
        $this->utility->generateToken();
        
        $this->utility->configureCookie("PHPSESSID", 0, isset($_SERVER['HTTPS']), false);
        
        $this->utilityPrivate->checkSessionOverTime();
        
        if ($_SESSION['user_activity'] != "") {
            $_SESSION['user_activity'] = "";
            
            header("location: ../web/index.php");
            
            exit;
        }
    }
    // Functions private
}