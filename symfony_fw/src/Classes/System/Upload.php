<?php
namespace App\Classes\System;

class Upload {
    // Vars
    private $response;
    
    private $utility;
    
    private $settings;
    
    private $maxSize;
    
    // Properties
    public function setSettings($value) {
        $value['mimeType'][] = "application/octet-stream";
        
        $value['mimeType'] = array_unique($value['mimeType']);
        
        $this->settings = $value;
    }
    
    // Functions public
    public function __construct($utility) {
        $this->response = Array();
        
        $this->utility = $utility;
        
        $this->settings = Array();
        
        $this->maxSize = 0;
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
                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("classUpload_1");
            }
            else {
                $this->response['status'] = "error";
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("classUpload_2");
            }
        }
        else {
            if ($action == "start") {
                $fileName = $_REQUEST['fileName'];
                
                if (file_exists("{$this->settings['path']}/$fileName") == true) {
                    $this->response['status'] = "error";
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("classUpload_3");
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
                    $mimeType = mime_content_type($tmpName);
                    $fileSize = $_FILES["file"]["size"];
                    $this->maxSize += $fileSize;
                    
                    $check = true;
                    
                    if (isset($this->settings['chunkSize']) == true && $fileSize > $this->settings['chunkSize']) {
                        $check = false;
                        
                        if (file_exists("{$this->settings['path']}/$fileName") == true)
                            unlink("{$this->settings['path']}/$fileName");
                        
                        $this->response['status'] = "error";
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("classUpload_4");
                    }
                    else if (isset($this->settings['mimeType']) == true && in_array($mimeType, $this->settings['mimeType']) == false) {
                        $check = false;
                        
                        if (file_exists("{$this->settings['path']}/$fileName") == true)
                            unlink("{$this->settings['path']}/$fileName");
                        
                        $this->response['status'] = "error";
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("classUpload_5");
                    }
                    else if (isset($this->settings['maxSize']) == true && $this->maxSize > $this->settings['maxSize']) {
                        $check = false;
                        
                        if (file_exists("{$this->settings['path']}/$fileName") == true)
                            unlink("{$this->settings['path']}/$fileName");
                        
                        $this->response['status'] = "error";
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("classUpload_6");
                    }
                    else if (isset($this->settings['imageSize']) == true) {
                        $imageSize = getimagesize($tmpName);
                        
                        if ($imageSize[0] > $this->settings['imageSize'][0] || $imageSize[1] > $this->settings['imageSize'][1]) {
                            $check = false;
                            
                            if (file_exists("{$this->settings['path']}/$fileName") == true)
                                unlink("{$this->settings['path']}/$fileName");
                            
                            $this->response['status'] = "error";
                            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("classUpload_7");
                        }
                    }
                    
                    if ($check == true) {
                        $content = file_get_contents($tmpName);
                        
                        file_put_contents("{$this->settings['path']}/$fileName", trim($content . PHP_EOL), FILE_APPEND);
                        
                        $this->response['status'] = "send";
                        $this->response['messages']['success'] = "";
                    }
                }
                else {
                    if (file_exists("{$this->settings['path']}/$fileName") == true)
                        unlink("{$this->settings['path']}/$fileName");
                    
                    $this->response['status'] = "error";
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("classUpload_8");
                }
            }
            else if ($action == "complete") {
                $extension = "";
                $fileName = $_REQUEST['fileName'];
                
                if (file_exists("{$this->settings['path']}/$fileName") == true) {
                    if (isset($this->settings['nameOverwrite']) == true && $this->settings['nameOverwrite'] != "") {
                        $extension = pathinfo("{$this->settings['path']}/$fileName", PATHINFO_EXTENSION);
                        
                        rename("{$this->settings['path']}/$fileName", "{$this->settings['path']}/{$this->settings['nameOverwrite']}.{$extension}");
                        
                        $fileName = $this->settings['nameOverwrite'];
                    }
                    
                    $this->response['status'] = "complete";
                    $this->response['fileName'] = $fileName;
                    $this->response['fileExtension'] = $extension;
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("classUpload_9");
                }
                else {
                    $this->response['status'] = "error";
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("classUpload_10");
                }
            }
        }
        
        return $this->response;
    }
}