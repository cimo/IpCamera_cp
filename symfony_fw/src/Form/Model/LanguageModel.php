<?php
namespace App\Form\Model;

class LanguageModel {
    // Vars
    private $codePage;
    
    // Properties
    public function setCodePage($value) {
        $this->codePage = $value;
    }
    
    // ---
    
    public function getCodePage() {
        return $this->codePage;
    }
}