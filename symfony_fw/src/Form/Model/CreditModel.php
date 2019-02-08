<?php
namespace App\Form\Model;

class CreditModel {
    // Vars
    private $credit;
    
    // Properties
    public function setCredit($value) {
        $this->credit = $value;
    }
    
    // ---
    
    public function getCredit() {
        return $this->credit;
    }
}