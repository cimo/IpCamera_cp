<?php
require_once("Utility.php");
require_once("Query.php");

class Utility {
    // Vars
    private $utility;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct() {
        $this->utility = new Utility();
        $this->query = new Query($this->database);
    }
    
    // Functions private
}