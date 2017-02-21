<?php
class Ajax {
    // Vars
    
    // Properties
    
    // Functions public
    public function __construct() {
    }
    
    public function response($array) {
        return json_encode($array, JSON_FORCE_OBJECT);
    }
    
    public function errors($elements) {
        $objectElements = (object) $elements;
        
        $errors = Array();

        foreach ($objectElements as $key => $value)
            $errors[$key] = $value;

        return $errors;
    }
    
    // Functions private
}