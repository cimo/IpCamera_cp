<?php
// Version 1.0.0

require_once("System/Utility.php");
require_once("Ajax.php");

class RecoverPassword {
    // Vars
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    
    // Properties
    
    // Functions public
    public function __construct() {
        $this->response = Array();
        
        $this->utility = new Utility();
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax();
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
                    
                    if ($_GET['controller'] == "dataCheckAction")
                        $this->dataCheckAction($parameters);
                    else if ($_GET['controller'] == "dataChangeAction")
                        $this->dataChangeAction($parameters);
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
    private function dataCheckAction($parameters) {
        $userRow = $this->query->selectUserDatabase($parameters['email']);

        if ($userRow != false) {
            $helpCode = $this->utility->generateRandomString(20);

            $url = $this->utility->getUrlRoot() . "/web/index.php?routeRecoverPassword=true&helpCode=" . $helpCode;

            // Send email to user
            $this->utility->sendEmail($userRow['email'],
                                        "Recover password",
                                        "<p>Click on this link for reset your password:</p>" .
                                        "<a href=\"$url\">$url</a>",
                                        $_SERVER['SERVER_ADMIN']);
            
            $query = $this->utility->getDatabase()->getPdo()->prepare("UPDATE users
                                                                        SET help_code = :helpCode
                                                                        WHERE id = :id");
            
            $query->bindValue(":helpCode", $helpCode);
            $query->bindValue(":id", $userRow['id']);
            
            $query->execute();

            $this->response['messages']['success'] = "Recover completed. Check your email for the instructions.";
        }
        else
            $this->response['messages']['error'] = "No user found with this email!";
    }
    
    private function dataChangeAction($parameters) {
        $userRow = $this->query->selectUserWithHelpCodeDatabase($parameters['helpCode']);
        
        if ($userRow != false) {
            $messagePassword = $this->utility->assigUserPassword("withoutOld", null, $parameters);
            
            if (isset($messagePassword['password']) == true) {
                $query = $this->utility->getDatabase()->getPdo()->prepare("UPDATE users
                                                                            SET password = :password,
                                                                                help_code = :helpCode
                                                                            WHERE id = :id");
                
                $query->bindValue(":password", $messagePassword['password']);
                $query->bindValue(":helpCode", null);
                $query->bindValue(":id", $userRow['id']);
                
                $query->execute();
                
                $this->response['messages']['success'] = "Change password completed.";
            }
            else
                $this->response['messages']['error'] = $messagePassword['error'];
        }
        else
            $this->response['messages']['error'] = "Recover not completed!";
    }
}