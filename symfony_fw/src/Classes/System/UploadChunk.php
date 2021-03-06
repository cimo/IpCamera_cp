<?php
namespace App\Classes\System;

class UploadChunk {
    // Vars
    private $helper;
    
    private $settings;
    
    private $maxSize;

    private $response;
    
    // Properties
    public function setSettings($value) {
        $this->settings = $value;
    }
    
    // Functions public
    public function __construct($helper) {
        $this->helper = $helper;

        $this->settings = Array();
        
        $this->maxSize = 0;

        $this->response = Array();
    }
    
    public function processFile() {
        $this->response = Array();
        
        $action = "";
        
        if (isset($_REQUEST['action']) == true)
            $action = $_REQUEST['action'];
        
        if ($action == "stop") {
            $fileName = $_REQUEST['fileName'];
            
            if (file_exists("{$this->settings['path']}/{$fileName}") == true) {
                unlink("{$this->settings['path']}/{$fileName}");
                
                $this->response['status'] = "stop";
                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("classUploadChunk_1");
            }
            else {
                $this->response['status'] = "error";
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("classUploadChunk_2");
            }
        }
        else {
            if ($action == "start") {
                $fileName = $_REQUEST['fileName'];

                if (isset($this->settings['replace']) == true && $this->settings['replace'] == true && file_exists("{$this->settings['path']}/{$fileName}") == true)
                    unlink("{$this->settings['path']}/{$fileName}");

                if (file_exists("{$this->settings['path']}/{$fileName}") == true) {
                    $this->response['status'] = "error";
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("classUploadChunk_3");
                }
                else {
                    touch("{$this->settings['path']}/{$fileName}");
                    
                    $this->response['status'] = "start";
                    $this->response['messages']['success'] = "";
                }
            }
            else if ($action == "send") {
                $fileName = $_REQUEST['fileName'];

                if (isset($_FILES["file"]) == true) {
                    $tmpName = $_FILES['file']['tmp_name'];
                    $mimeType = mime_content_type($tmpName);
                    $fileSize = $_FILES["file"]["size"];
                    $this->maxSize += $fileSize;

                    $check = true;
                    
                    if (isset($this->settings['chunkSize']) == true && $fileSize > $this->settings['chunkSize']) {
                        $check = false;
                        
                        if (file_exists("{$this->settings['path']}/{$fileName}") == true)
                            unlink("{$this->settings['path']}/{$fileName}");
                        
                        $this->response['status'] = "error";
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("classUploadChunk_4");
                    }
                    else if (isset($this->settings['mimeType']) == true && in_array($mimeType, $this->settings['mimeType']) == false) {
                        $check = false;
                        
                        if (file_exists("{$this->settings['path']}/{$fileName}") == true)
                            unlink("{$this->settings['path']}/{$fileName}");
                        
                        $this->response['status'] = "error";
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("classUploadChunk_5");
                    }
                    else if (isset($this->settings['maxSize']) == true && $this->maxSize > $this->settings['maxSize']) {
                        $check = false;
                        
                        if (file_exists("{$this->settings['path']}/{$fileName}") == true)
                            unlink("{$this->settings['path']}/{$fileName}");
                        
                        $this->response['status'] = "error";
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("classUploadChunk_6");
                    }
                    else if (isset($this->settings['imageSize']) == true) {
                        $imageSize = getimagesize($tmpName);
                        
                        if ($imageSize[0] > $this->settings['imageSize'][0] || $imageSize[1] > $this->settings['imageSize'][1]) {
                            $check = false;
                            
                            if (file_exists("{$this->settings['path']}/{$fileName}") == true)
                                unlink("{$this->settings['path']}/{$fileName}");
                            
                            $this->response['status'] = "error";
                            $this->response['messages']['error'] = $this->helper->getTranslator()->trans("classUploadChunk_7");
                        }
                    }
                    
                    if ($check == true) {
                        $content = file_get_contents($tmpName);
                        
                        file_put_contents("{$this->settings['path']}/{$fileName}", trim($content), FILE_APPEND);
                        
                        $this->response['status'] = "send";
                        $this->response['messages']['success'] = "";
                    }
                }
                else {
                    if (file_exists("{$this->settings['path']}/{$fileName}") == true)
                        unlink("{$this->settings['path']}/{$fileName}");
                    
                    $this->response['status'] = "error";
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("classUploadChunk_8");
                }
            }
            else if ($action == "complete") {
                $fileName = $_REQUEST['fileName'];
                
                if (file_exists("{$this->settings['path']}/{$fileName}") == true) {
                    $extension = pathinfo("{$this->settings['path']}/{$fileName}", PATHINFO_EXTENSION);
                    $extension = $extension != "" ? ".{$extension}" : "";
                    
                    if (isset($this->settings['nameOverwrite']) == true && $this->settings['nameOverwrite'] != "") {
                        rename("{$this->settings['path']}/{$fileName}", "{$this->settings['path']}/{$this->settings['nameOverwrite']}{$extension}");
                        
                        $fileName = $this->settings['nameOverwrite'];
                    }
                    
                    $this->response['status'] = "complete";
                    $this->response['fileName'] = $fileName;
                    $this->response['fileExtension'] = $extension;
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("classUploadChunk_9");
                }
                else {
                    $this->response['status'] = "error";
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("classUploadChunk_10");
                }
            }
        }
        
        return $this->response;
    }
}