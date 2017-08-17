<?php
require_once("Utility.php");

class UtilityPrivate {
    // Vars
    private $utility;
    
    // Properties
    
    // Functions public
    public function __construct() {
        $this->utility = new Utility();
    }
    
    public function jsonParametersParse($json) {
        $parameters = Array();
        
        foreach($json as $key => $value) {
            preg_match('#\[(.*?)\]#', $value->name, $match);
            
            if (count($match) == 0)
                $parameters[$key] = $value;
            else
                $parameters[$match[1]] = $value->value;
        }
        
        return $parameters;
    }
    
    public function createTemplatesList() {
        $templatesPath = "{$this->utility->getPathRoot()}/Resources/public/images/templates";
        
        $scanDirElements = scandir($templatesPath);
        
        $list = Array();
        
        if ($scanDirElements != false) {
            foreach ($scanDirElements as $key => $value) {
                if ($value != "." && $value != ".." && $value != ".htaccess" && is_dir("$templatesPath/$value") == true)
                    $list[$value] = $value;
            }
        }
        
        return $list;
    }
    
    public function createMotionVersionList() {
        $list = Array("3.1.12", "4.0.1");
        
        return $list;
    }
    
    public function checkToken($token) {
        if (isset($_SESSION['token']) == true && $token == $_SESSION['token'])
            return true;
        
        return false;
    }
    
    public function checkSessionOverTime($root = false) {
        if ($root == true) {
            if (isset($_SESSION['user_activity']) == false) {
                $_SESSION['user_activity_count'] = 0;
                $_SESSION['user_activity'] = "";
            }
        }
        
        if (isset($_SESSION['user_id']) == true) {
            if (isset($_SESSION['timestamp']) == false)
                $_SESSION['timestamp'] = time();
            else {
                $timeLapse = time() - $_SESSION['timestamp'];

                if ($timeLapse > $this->utility->getSessionMaxIdleTime()) {
                    $userActivity = "Session time is over, please refresh the page.";
                    
                    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) == false && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest") {
                        echo json_encode(Array(
                            'userActivity' => $userActivity
                        ));

                        exit();
                    }
                    else {
                        $this->utility->sessionUnset();
                        
                        header("location: ../web/index.php");
                    }
                    
                    $_SESSION['user_activity'] = $userActivity;
                    
                    unset($_SESSION['timestamp']);
                }
                else
                    $_SESSION['timestamp'] = time();
            }
        }
            
        if (isset($_SESSION['user_activity']) == true) {
            if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) == false && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest" && $_SESSION['user_activity'] != "") {
                echo json_encode(Array(
                    'userActivity' => $_SESSION['user_activity']
                ));

                exit;
            }
        }
        
        if ($root == true && $_SESSION['user_activity'] != "") {
            $_SESSION['user_activity_count'] ++;

            if ($_SESSION['user_activity_count'] > 2) {
                $_SESSION['user_activity_count'] = 0;
                $_SESSION['user_activity'] = "";
            }
        }
    }
    
    // Functions private
}