<?php
namespace App\Form\Model;

class SearchModel {
    // Vars
    private $words;
    
    // Properties
    public function setWords($value) {
        $this->words = $value;
    }
    
    // ---
    
    public function getWords() {
        return $this->words;
    }
}