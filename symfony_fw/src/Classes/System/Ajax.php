<?php
namespace App\Classes\System;

use Symfony\Component\HttpFoundation\JsonResponse;

class Ajax {
    // Vars
    private $helper;
    
    private $session;
    
    // Properties
    
    // Functions public
    public function __construct($helper) {
        $this->helper = $helper;
        
        $this->session = $this->helper->getSession();
    }
    
    public function response($array) {
        return new JsonResponse($array);
    }
    
    public function errors($elements) {
        if (is_string($elements) == false) {
            $errors = Array();
            
            foreach ($elements->getErrors() as $error)
                $errors[] = $this->helper->getTranslator()->trans($error->getMessage());
            
            foreach ($elements->all() as $key => $value)
                $errors[$key] = $this->errors($value);

            return $errors;
        }
        else
            return $this->helper->getTranslator()->trans($elements);
        
        return "";
    }
    
    // Functions private
}