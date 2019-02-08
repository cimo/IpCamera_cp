<?php
namespace App\Classes\System;

class TwigExtension extends \Twig_Extension {
    // Vars
    
    // Properties
    public function getFunctions() {
        return Array(
            new \Twig_SimpleFunction("file_exists", "file_exists")
        );
    }
    
    public function getName() {
        return "uebusaito_file_exists";
    }
    
    // Functions public
    
    // Functions private
}