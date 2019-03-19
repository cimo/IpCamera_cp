<?php
namespace App\Classes\System;

class Upload {
    // Vars
    private $response;
    
    private $utility;
    
    private $settings;
    
    // Properties
    public function setSettings($value) {
        $this->settings = $value;
    }
    
    // Functions public
    public function __construct($utility) {
        $this->response = Array();
        
        $this->utility = $utility;
        
        $this->settings = Array();
    }
    
    public function processFile() {
        $this->response = Array();
        
        $action = "";
        
        if (isset($_REQUEST['action']) == true)
            $action = $_REQUEST['action'];
        
        if ($action == "stop") {
            $fileName = $_REQUEST['fileName'];
            
            if (file_exists("{$this->settings['path']}/$fileName") == true) {
                unlink("{$this->settings['path']}/$fileName");
                
                $this->response['status'] = "stop";
                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("classUpload_6");
            }
            else {
                $this->response['status'] = "error";
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("classUpload_7");
            }
        }
        else {
            if ($action == "start") {
                $fileName = $_REQUEST['fileName'];
                
                if (file_exists("{$this->settings['path']}/$fileName") == true) {
                    $this->response['status'] = "error";
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("classUpload_8");
                }
                else {
                    touch("{$this->settings['path']}/$fileName");
                    
                    $this->response['status'] = "start";
                    $this->response['messages']['success'] = "";
                }
            }
            else if ($action == "send") {
                if (isset($_FILES["file"]) == true) {
                    $fileName = $_REQUEST['fileName'];
                    
                    $tmpName = $_FILES['file']['tmp_name'];
                    $fileSize = $_FILES["file"]["size"];
                    
                    $content = file_get_contents($tmpName);
                    
                    file_put_contents("{$this->settings['path']}/$fileName", trim($content . PHP_EOL), FILE_APPEND);
                    
                    $this->response['status'] = "send";
                    $this->response['messages']['success'] = "";
                }
                else {
                    $this->response['status'] = "error";
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("classUpload_9");
                }
            }
            else if ($action == "complete") {
                $fileName = $_REQUEST['fileName'];
                
                if (file_exists("{$this->settings['path']}/$fileName") == true) {
                    $this->response['status'] = "complete";
                    $this->response['fileName'] = $fileName;
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("classUpload_10");
                }
                else {
                    $this->response['status'] = "error";
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("classUpload_11");
                }
            }
        }
        
        return $this->response;
    }
}