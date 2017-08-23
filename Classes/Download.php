<?php
require_once("Utility.php");

class Download {
    // Vars
    private $utility;
    
    // Properties
    
    // Functions public
    public function __construct() {
        $this->utility = new Utility();
    }
    
    public function output() {
        $token = isset($_GET['token']) == true ? $_GET['token'] : "";
        $path = isset($_GET['path']) == true ? $_GET['path'] : "";
        $name = isset($_GET['name']) == true ? trim($_GET['name']) : "";
        
        $filePath = "$path/$name";
        
        if ($this->utility->checkToken($token) == true) {
            if (file_exists($filePath) == true) {
                $mimeContentType = mime_content_type($filePath);

                header("Content-Description: File Transfer");
                header("Content-Type: $mimeContentType");
                header("Content-Disposition: attachment; filename=\"" . basename($filePath)."\"");
                header("Content-Transfer-Encoding: binary");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, pre-check=0, post-check=0");
                header("Pragma: public");
                header("Content-Length: " . filesize($filePath));

                readfile($filePath);
                
                return;
            }
        }
        
        echo "File not founded!";
    }
    
    // Functions private
}