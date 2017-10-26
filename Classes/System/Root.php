<?php
require_once("Utility.php");
require_once(dirname(__DIR__) . "/Ajax.php");
require_once(dirname(__DIR__) . "/IpCameraUtility.php");
require_once(dirname(__DIR__) . "/IpCamera.php");

class Root {
    // Vars
    private $utility;
    private $ajax;
    private $ipCameraUtility;
    private $ipCamera;
    
    // Properties
    public function getUtility() {
        return $this->utility;
    }
    
    public function getAjax() {
        return $this->ajax;
    }
    
    public function getIpCameraUtility() {
        return $this->ipCameraUtility;
    }
    
    public function getIpCamera() {
        return $this->ipCamera;
    }
    
    // Functions public
    public function __construct() {
        $this->utility = new Utility();
        $this->ajax = new Ajax();
        $this->ipCameraUtility = new IpCameraUtility();
        $this->ipCamera = new IpCamera();
        
        $this->utility->generateToken();
        
        $this->utility->configureCookie(session_name(), 0, false, true);
        
        $this->utility->checkSessionOverTime(true);
        
        // Logic
        $event = isset($_POST['event']) == true ? $_POST['event'] : "";
        
        if ($event == "your_event") {
            echo $this->ajax->response(Array(
                'response' => $this->response
            ));
            
            exit();
        }
    }
    // Functions private
}