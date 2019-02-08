<?php
namespace App\Form\Model;

class RecoverPasswordModel {
    // Vars
    private $email;
    
    // Properties
    public function setEmail($value) {
        $this->email = $value;
    }
    
    // ---
    
    public function getEmail() {
        return $this->email;
    }
}