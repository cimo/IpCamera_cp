<?php
namespace App\Classes\System;

use Symfony\Component\HttpFoundation\JsonResponse;

class Ajax {
    // Vars
    private $utility;
    
    // Properties
    
    // Functions public
    public function __construct($utility) {
        $this->utility = $utility;
    }
    
    public function response($array) {
        return new JsonResponse($array);
    }
    
    public function errors($elements) {
        if (is_string($elements) == false) {
            $errors = Array();
            
            foreach ($elements->getErrors() as $error)
                $errors[] = $this->utility->getTranslator()->trans($error->getMessage());
            
            foreach ($elements->all() as $key => $value)
                $errors[$key] = $this->errors($value);

            return $errors;
        }
        else
            return $this->utility->getTranslator()->trans($elements);
        
        return "";
    }
    
    // Functions private
}