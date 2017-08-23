<?php
require_once("Utility.php");

class Root {
    // Vars
    private $utility;
    
    // Properties
    
    // Functions public
    public function __construct() {
        $this->utility = new Utility();
        
        $this->utility->generateToken();
        
        $this->utility->configureCookie(session_name(), 0, false, true);
        
        $this->utility->checkSessionOverTime(true);
    }
    // Functions private
}