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
        
        $this->utility->configureCookie("ipcamera_cp", 0, false, true);
        
        $this->utilityPrivate->checkSessionOverTime(true);
    }
    // Functions private
}