<?php
namespace App\Classes\System;

class Upload {
    // Vars
    private $utility;
    
    private $settings;
    
    private $tmp;
    private $name;
    
    // Properties
    public function setSettings($value) {
        $this->settings = $value;
    }
    
    // Functions public
    public function __construct($utility) {
        $this->utility = $utility;
        
        $this->settings = Array();
        
        $this->tmp = "0";
        $this->name = "";
    }
    
    public function processFile() {
        $action = "";
        
        if (isset($_GET['action']) == true)
            $action = $_GET['action'];
        
        if (isset($_GET['tmp']) == true)
            $this->tmp = $_GET['tmp'];
        
        if (isset($_GET['name']) == true)
            $this->name = $_GET['name'];
        
        if ($action == "change")
            return $this->change();
        else if ($action == "start")
            return $this->start();
        else if ($action == "finish")
            return $this->finish();
        else if ($action == "abort")
            return $this->abort();
    }
    
    // Functions private
    private function change() {
        if (isset($_FILES["file"]) == true) {
            $fileSize = $_FILES["file"]["size"];
            $fileName = basename($_FILES["file"]["name"]);

            if (isset($this->settings['imageWidth']) == true && isset($this->settings['imageHeight']) == true) {
                $imageSize = getimagesize($_FILES["file"]["tmp_name"]);

                if ($imageSize[0] > $this->settings['imageWidth']  ||  $imageSize[1] > $this->settings['imageHeight']) {
                    return Array(
                        'status' => 1,
                        'text' => $this->utility->getTranslator()->trans("classUpload_3") . "{$this->settings['imageWidth']} px - {$this->settings['imageHeight']} px."
                    );
                }
            }

            if (isset($this->settings['maxSize']) == true && $fileSize > $this->settings['maxSize']) {
                return Array(
                    'status' => 1,
                    'text' => $this->utility->getTranslator()->trans("classUpload_1") . $this->utility->unitFormat($this->settings['maxSize']) . "."
                );
            }

            if (in_array(mime_content_type($_FILES["file"]["tmp_name"]), $this->settings['types']) == false) {
                return Array(
                    'status' => 1,
                    'text' => $this->utility->getTranslator()->trans("classUpload_2") . implode(", ", $this->settings['types']) . "."
                );
            }
        }
        else {
            return Array(
                'status' => 1,
                'text' => $this->utility->getTranslator()->trans("classUpload_7")
            );
        }
        
        return $this->settings['chunkSize'];
    }
    
    private function start() {
        if ($this->tmp == "0")
            $this->tmp = uniqid(mt_rand(), true) . ".tmp";
        
        $content = file_get_contents("php://input");
	
        $fopen = fopen("{$this->settings['path']}/$this->tmp", "a");

        if ($this->checkChunkSize($this->settings['path']) == false) {
            fclose($fopen);

            return Array(
                'status' => 1,
                'text' => $this->utility->getTranslator()->trans("classUpload_4")
            );
        }
        else {
            fwrite($fopen, $content);
            fclose($fopen);

            if (empty($this->settings['path']) == false) {
                return Array(
                    'status' => 0,
                    'tmp' => $this->tmp
                );
            }
        }
    }
    
    private function finish() {
        if (file_exists("{$this->settings['path']}/$this->tmp") == true) {
            if (isset($this->settings['nameOverwrite']) == true && $this->settings['nameOverwrite'] != "")
                $this->name =  $this->settings['nameOverwrite'] . "." . pathinfo($this->name, PATHINFO_EXTENSION);
            
            rename("{$this->settings['path']}/$this->tmp", "{$this->settings['path']}/$this->name");
            
            if (empty($this->settings['path']) == false) {
                return Array(
                    'status' => 2,
                    'text' => $this->utility->getTranslator()->trans("classUpload_5"),
                    'name' => $this->name
                );
            }
        }
        else {
            return Array(
                'status' => 2,
                'text' => $this->utility->getTranslator()->trans("classUpload_6")
            );
        }
    }
    
    private function abort() {
        if (file_exists("{$this->settings['path']}/$this->tmp") == true)
            unlink("{$this->settings['path']}/$this->tmp");
        
        return Array(
            'status' => 2,
            'text' => $this->utility->getTranslator()->trans("classUpload_7")
        );
    }
    
    private function checkChunkSize($value) {
        $fopen = fopen($value . "/check_" . $this->tmp, "a");
        $fstat = fstat($fopen);
        $size = array_slice($fstat, 13)['size'];
        fclose($fopen);

        if ($size > $this->settings['chunkSize']) {
            unlink($value . "/check_" . $this->tmp);

            return false;
        }
        else
            unlink($value . "/check_" . $this->tmp);
        
        return true;
    }
}