<?php
require_once("Utility.php");

class UtilityPrivate {
    // Vars
    private $utility;
    
    // Properties
    
    // Functions public
    public function __construct() {
        $this->utility = new Utility();
    }
    
    public function createTemplatesList() {
        $templatesPath = "{$this->utility->getPathRoot()}/Resources/public/images/templates";
        
        $scanDirElements = scandir($templatesPath);
        
        $list = Array();
        
        if ($scanDirElements != false) {
            foreach ($scanDirElements as $key => $value) {
                if ($value != "." && $value != ".." && $value != ".htaccess" && is_dir("$templatesPath/$value") == true)
                    $list[$value] = $value;
            }
        }
        
        return $list;
    }
    
    public function createMotionVersionList() {
        $list = Array("3.1.12", "4.0.1");
        
        return $list;
    }
    
    // Functions private
}