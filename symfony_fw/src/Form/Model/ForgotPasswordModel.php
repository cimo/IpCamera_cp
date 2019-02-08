<?php
namespace App\Form\Model;

class ForgotPasswordModel {
    // Vars
    private $password;
    private $passwordConfirm;
    
    // Properties
    public function setPassword($value) {
        $this->password = $value;
    }
    
    public function setPasswordConfirm($value) {
        $this->passwordConfirm = $value;
    }
    
    // ---
    
    public function getPassword() {
        return $this->password;
    }
    
    public function getPasswordConfirm() {
        return $this->passwordConfirm;
    }
}