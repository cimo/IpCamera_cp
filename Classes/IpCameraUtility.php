<?php
// Version 1.0.0

require_once("System/Utility.php");

class IpCameraUtility {
    // Vars
    private $utility;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct() {
        $this->utility = new Utility();
        $this->query = $this->utility->getQuery();
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
    
    public function createRoleUserHtml($selectId, $isRequired = false) {
        $rows = $this->query->selectAllRoleUserDatabase();
        
        $required = $isRequired == true ? "required=\"required\"" : "";
        
        $html = "<select id=\"$selectId\" class=\"form-control\" $required>
            <option value=\"\">Select</option>";
            foreach($rows as $key => $value)
                $html .= "<option value=\"{$value['id']}\">{$value['level']}</option>";
        $html .= "</select>";
        
        return $html;
    }
    
    public function checkAttemptLogin($type, $userValue, $settingRow) {
        $row = $this->query->selectUserDatabase($userValue);
        
        $dateTimeCurrentLogin = new \DateTime($row['date_current_login']);
        $dateTimeCurrent = new \DateTime();
        
        $interval = intval($dateTimeCurrentLogin->diff($dateTimeCurrent)->format("%i"));
        $total = $settingRow['login_attempt_time'] - $interval;
        
        if ($total < 0)
            $total = 0;
        
        $dateCurrent = date("Y-m-d H:i:s");
        $dateLastLogin = strpos($row['date_last_login'], "0000") !== false ? $dateCurrent : $row['date_current_login'];
        
        $result = Array("", "");
        
        if (isset($row['id']) == true && $settingRow['login_attempt_time'] > 0) {
            $count = $row['attempt_login'] + 1;
            
            $query = $this->utility->getDatabase()->getPdo()->prepare("UPDATE users
                                                                        SET date_current_login = :dateCurrentLogin,
                                                                            date_last_login = :dateLastLogin,
                                                                            ip = :ip,
                                                                            attempt_login = :attemptLogin
                                                                        WHERE id = :id");
            
            if ($type == "success") {
                if ($count > $settingRow['login_attempt_count'] && $total > 0) {
                    $result[0] = "lock";
                    $result[1] = $total;
                    
                    return Array(false, $result[0], $result[1]);
                }
                else {
                    $query->bindValue(":dateCurrentLogin", $dateCurrent);
                    $query->bindValue(":dateLastLogin", $dateLastLogin);
                    $query->bindValue(":ip", $this->utility->clientIp());
                    $query->bindValue(":attemptLogin", 0);
                    $query->bindValue(":id", $row['id']);

                    $query->execute();
                }
            }
            else if ($type == "failure") {
                if ($count > $settingRow['login_attempt_count'] && $total > 0) {
                    $result[0] = "lock";
                    $result[1] = $total;
                }
                else {
                    if ($count > $settingRow['login_attempt_count'])
                        $count = 1;
                    
                    $query->bindValue(":dateCurrentLogin", $dateCurrent);
                    $query->bindValue(":dateLastLogin", $row['date_last_login']);
                    $query->bindValue(":ip", $this->utility->clientIp());
                    $query->bindValue(":attemptLogin", $count);
                    $query->bindValue(":id", $row['id']);
                    
                    $query->execute();
                    
                    $result[0] = "try";
                    $result[1] = $count;
                }
                
                return Array(false, $result[0], $result[1]);
            }
        }
        
        return Array(true, $result[0], $result[1]);
    }
    
    public function checkRoleUser($roleName, $roleId) {
        $row = $this->query->selectRoleUserDatabase($roleId);
        
        foreach ($roleName as $key => $value) {
            if (in_array($value, $row) == true) {
                return true;

                break;
            }
        }
        
        return false;
    }
    
    public function checkInRoleUser($roleIdFirst, $roleIdSecond) {
        $roleIdFirstExplode = explode(",", $roleIdFirst);
        array_pop($roleIdFirstExplode);

        $roleIdSecondExplode =  explode(",", $roleIdSecond);
        array_pop($roleIdSecondExplode);
        
        if ($this->utility->valueInSubArray($roleIdFirstExplode, $roleIdSecondExplode) == true)
            return true;
        
        return false;
    }
    
    public function assigUserPassword($type, $user, $parameters) {
        $result = Array();
        
        if ($type == "withOld") {
            $row = $this->query->selectUserDatabase($user['id']);
            
            if (password_verify($parameters['old'], $row['password']) == false)
                $result['error'] = "Old password doesn't match!";
            else if ($parameters['new'] == "" || $parameters['newConfirm'] == "" || $parameters['new'] != $parameters['newConfirm'])
                $result['error'] = "New password and New confirm password doesn't match!";
            else
                $result['password'] = $this->createPasswordEncoder($type, $parameters);
        }
        else if ($type == "withoutOld") {
            if ($parameters['password'] != "" || $parameters['passwordConfirm'] != "") {
                if ($parameters['password'] == "" || $parameters['passwordConfirm'] == "" || $parameters['password'] != $parameters['passwordConfirm'])
                    $result['error'] = "Password and Confirm password doesn't match!";
                else
                    $result['password'] = $this->createPasswordEncoder($type, $parameters);
            }
            else
                $result['password'] = "";
        }
        
        return $result;
    }
    
    // Functions private
    private function createPasswordEncoder($type, $parameters) {
        if ($type == "withOld")
            return password_hash($parameters['new'], PASSWORD_DEFAULT);
        else if ($type == "withoutOld")
            return password_hash($parameters['password'], PASSWORD_DEFAULT);
    }
}