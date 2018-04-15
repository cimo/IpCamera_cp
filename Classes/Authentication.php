<?php
// Version 1.0.0

require_once("System/Utility.php");
require_once("Ajax.php");

class Authentication {
    // Vars
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    
    private $settingRow;
    
    // Properties
    
    // Functions public
    public function __construct() {
        $this->response = Array();
        
        $this->utility = new Utility();
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax();
        
        $this->settingRow = $this->query->selectSettingDatabase();
    }
    
    public function phpInput() {
        $this->utility->checkSessionOverTime();
        
        $content = file_get_contents("php://input");
        $json = json_decode($content);

        if ($json != null) {
            if (isset($_GET['controller']) == true) {
                $token = is_array($json) == true ? end($json)->value : $json->token;

                if ($this->utility->checkToken($token) == true) {
                    $parameters = $this->utility->requestParametersParse($json);
                    
                    if ($_GET['controller'] == "authenticationEnterCheckAction")
                        $this->authenticationEnterCheckAction($parameters);
                    else if ($_GET['controller'] == "authenticationExitCheckAction")
                        $this->authenticationExitCheckAction();
                }
            }
        }
        else
            $this->response['messages']['error'] = "Json error!";
        
        echo $this->ajax->response(Array(
            'response' => $this->response
        ));
        
        $this->utility->getDatabase()->close();
    }
    
    // Functions private
    private function authenticationEnterCheckAction($parameters) {
        $userRow = $this->query->selectUserDatabase($parameters['_username']);
        
        if ($userRow != false) {
            if (password_verify($parameters['_password'], $userRow['password']) == true) {
                $checkAttemptLogin = $this->utility->checkAttemptLogin("success", $userRow['id'], $this->settingRow);
                
                if ($checkAttemptLogin[0] == true) {
                    if (isset($parameters['_remember_me']) == true)
                        setcookie(session_name() . "_REMEMBERME", $parameters['_remember_me'], time() + 3600 * 24 * 365, "/");
                    
                    $_SESSION['userLogged'] = $userRow;
                    $_SESSION['userLogged']['userRoleRow'] = $this->query->selectRoleUserDatabase($_SESSION['userLogged']['role_user_id'], true);

                    $this->response['values'] = "logged";
                }
                else {
                    if ($checkAttemptLogin[1] == "lock")
                        $this->response['messages']['error'] = "A lot attempts, wait {$checkAttemptLogin[2]} minutes before retrying.";
                }
            }
            else {
                if ($this->utility->checkUserNotLocked($userRow['username']) == false)
                    $message = "Your account is locked.";
                else {
                    $checkAttemptLogin = $this->utility->checkAttemptLogin("failure", $userRow['username'], $this->settingRow);

                    if ($checkAttemptLogin[1] == "lock")
                        $message = "A lot attempts, wait {$checkAttemptLogin[2]} minutes before retrying.";
                    else if ($checkAttemptLogin[1] == "try")
                        $message = "Attempt: {$checkAttemptLogin[2]} / {$this->settingRow['login_attempt_count']}";
                }
                
                $this->response['messages']['error'] = $message;
            }
        }
        else
            $this->response['messages']['error'] = "Bad credentials!";
    }
    
    private function authenticationExitCheckAction() {
        if (isset($_COOKIE[session_name() . '_REMEMBERME']) == true) {
            unset($_COOKIE[session_name() . '_REMEMBERME']);
            
            setcookie(session_name() . "_REMEMBERME", null, -1, "/");
        }
        
        unset($_SESSION['userLogged']);
        
        $this->response['values'] = "unlogged";
    }
}