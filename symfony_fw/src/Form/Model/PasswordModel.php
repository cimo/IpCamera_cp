<?php
namespace App\Form\Model;

class PasswordModel {
    // Vars
    private $old;
    private $new;
    private $newConfirm;
    
    // Properties
    public function setOld($value) {
        $this->old = $value;
    }
    
    public function setNew($value) {
        $this->new = $value;
    }
    
    public function setNewConfirm($value) {
        $this->newConfirm = $value;
    }
    
    // ---
    
    public function getOld() {
        return $this->old;
    }
    
    public function getNew() {
        return $this->new;
    }
    
    public function getNewConfirm() {
        return $this->newConfirm;
    }
}