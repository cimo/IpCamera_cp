<?php
require_once("Utility.php");
require_once("Query.php");

class UtilityPrivate {
    // Vars
    private $utility;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct() {
        $this->utility = new Utility();
        $this->query = new Query($this->utility->getDatabase());
    }
    
    public function checkToken($token) {
        if (isset($_SESSION['token']) == true && $token == $_SESSION['token'])
            return true;
        
        return false;
    }
    
    public function checkSessionOverTime($root = false) {
        if ($root == true) {
            if (isset($_SESSION['user_activity']) == false) {
                $_SESSION['user_activity'] = "";
                
                $_SESSION['count_root'] = 0;
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
            $_SESSION['count_root'] ++;

            if ($_SESSION['count_root'] > 2) {
                $_SESSION['user_activity'] = "";
                
                $_SESSION['count_root'] = 0;
            }
        }
    }
    
    // Functions private
}