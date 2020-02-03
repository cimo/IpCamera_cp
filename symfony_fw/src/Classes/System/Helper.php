<?php
namespace App\Classes\System;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use App\Config;
use App\Classes\System\Query;

class Helper {
    // Vars
    private $container;
    private $entityManager;
    
    private $authorizationChecker;
    private $tokenStorage;
    private $session;
    private $connection;
    private $translator;
    private $passwordEncoder;
    
    private $sessionMaxIdleTime;
    
    private $config;
    private $query;
    
    private $settingRow;
    
    private $protocol;
    
    private $pathRoot;
    private $pathSrc;
    private $pathPublic;
    
    private $urlRoot;
    
    private $supportSymlink;
    
    private $websiteFile;
    private $websiteName;
    
    // Properties
    public function getAuthorizationChecker() {
        return $this->authorizationChecker;
    }
    
    public function getTokenStorage() {
        return $this->tokenStorage;
    }
    
    public function getSession() {
        return $this->session;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function getTranslator() {
        return $this->translator;
    }
    
    public function getPasswordEncoder() {
        return $this->passwordEncoder;
    }
    
    // ---
    
    public function getSessionMaxIdleTime() {
        return $this->sessionMaxIdleTime;
    }
    
    public function getQuery() {
        return $this->query;
    }
    
    public function getSettingRow() {
        return $this->settingRow;
    }
    
    public function getProtocol() {
        return $this->protocol;
    }
    
    public function getPathRoot() {
        return $this->pathRoot;
    }
    
    public function getPathSrc() {
        return $this->pathSrc;
    }
    
    public function getPathPublic() {
        return $this->pathPublic;
    }
    
    public function getUrlRoot() {
        return $this->urlRoot;
    }
    
    public function getSupportSymlink() {
        return $this->supportSymlink;
    }
    
    public function getWebsiteFile() {
        return $this->websiteFile;
    }
    
    public function getWebsiteName() {
        return $this->websiteName;
    }
    
    // Functions public
    public function __construct($container, $entityManager, $translator, $passwordEncoder = null) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->authorizationChecker = $this->container->get("security.authorization_checker");
        $this->tokenStorage = $this->container->get("security.token_storage");
        $this->session = $this->container->get("session");
        $this->connection = $this->entityManager->getConnection();
        $this->translator = $translator;
        $this->passwordEncoder = $passwordEncoder;
        
        $this->sessionMaxIdleTime = 3600;
        
        $this->config = new Config();
        $this->query = new Query($this->connection);
        
        $this->settingRow = $this->query->selectSettingDatabase();
        
        $this->protocol = $this->config->getProtocol();
        
        $this->pathRoot = $_SERVER['DOCUMENT_ROOT'] . $this->config->getPathRoot();
        $this->pathSrc = "{$this->pathRoot}/src";
        $this->pathPublic = "{$this->pathRoot}/public";
        
        $this->urlRoot = $this->config->getProtocol() . $_SERVER['HTTP_HOST'] . $this->config->getUrlRoot();
        
        $this->supportSymlink = $this->config->getSupportSymlink();
        
        $this->websiteFile = $this->config->getFile();
        $this->websiteName = $this->config->getName();
        
        $this->arrayColumnFix();
    }
    
    public function createUserSelectHtml($selectId, $label, $isRequired = false) {
        $rows = $this->query->selectAllUserDatabase();
        
        $required = $isRequired == true ? "required=\"required\"" : "";
        
        $html = "<div id=\"$selectId\" class=\"mdc-select\" $required>
            <select class=\"mdc-select__native-control\">
                <option value=\"\"></option>";
                foreach ($rows as $key => $value) {
                    $html .= "<option value=\"{$value['id']}\">{$value['username']}</option>";
                }
            $html .= "</select>
            <label class=\"mdc-floating-label mdc-floating-label--float-above\">" . $this->translator->trans($label) . "</label>
            <div class=\"mdc-line-ripple\"></div>
        </div>";
        
        return $html;
    }
    
    public function createUserRoleSelectHtml($selectId, $label, $isRequired = false) {
        $rows = $this->query->selectAllRoleUserDatabase();
        
        $required = $isRequired == true ? "required=\"required\"" : "";
        
        $html = "<div id=\"$selectId\" class=\"mdc-select\" $required>
            <select class=\"mdc-select__native-control\">
                <option value=\"\"></option>";
                foreach ($rows as $key => $value) {
                    $html .= "<option value=\"{$value['id']}\">{$value['level']}</option>";
                }
            $html .= "</select>
            <label class=\"mdc-floating-label mdc-floating-label--float-above\">" . $this->translator->trans($label) . "</label>
            <div class=\"mdc-line-ripple\"></div>
        </div>";
        
        return $html;
    }
    
    public function createPageSelectHtml($urlLocale, $selectId, $label, $draft = false) {
        $rows = $this->query->selectAllPageDatabase($urlLocale, null, $draft);
        
        $pageList = $this->createPageList($rows, true);
        
        $html = "<div id=\"$selectId\" class=\"mdc-select\">
            <select class=\"mdc-select__native-control\">
                <option value=\"\"></option>";
                foreach ($pageList as $key => $value) {
                    $html .= "<option value=\"$key\">$value</option>";
                }
            $html .= "</select>
            <label class=\"mdc-floating-label mdc-floating-label--float-above\">$label</label>
            <div class=\"mdc-line-ripple\"></div>
        </div>";
        
        return $html;
    }
    
    public function createLanguageSelectOptionHtml($code) {
        $row = $this->query->selectLanguageDatabase($code);
        $rows = $this->query->selectAllLanguageDatabase();
        
        $key = array_search($row, $rows);
        unset($rows[$key]);
        array_unshift($rows, $row);
        
        $html = "";
        
        foreach ($rows as $key => $value) {
            $html .= "<option value=\"{$value['code']}\">{$value['code']}</option>";
        }
        
        return $html;
    }
    
    public function createPageSortListHtml($rows, $draft = false) {
        $html = "<ul class=\"sort_list\">";
            foreach ($rows as $key => $value) {
                $html .= "<li class=\"ui-state-default\">
                    <div class=\"mdc-chip\">
                        <i class=\"material-icons mdc-chip__icon mdc-chip__icon--leading\">drag_handle</i>
                        <div class=\"mdc-chip__text sort_elemet_data\" data-id=\"$key\">[$key] $value</div>
                    </div>
                </li>";
            }
            
            if ($this->session->get("pageProfileId") == 0) {
                $pageRows = $this->query->selectAllPageDatabase($this->session->get("languageTextCode"), null, $draft);
                $id = count($pageRows) + 1;
                
                $html .= "<li class=\"ui-state-default\">
                    <div class=\"mdc-chip\">
                        <i class=\"material-icons mdc-chip__icon mdc-chip__icon--leading\">drag_handle</i>
                        <div class=\"mdc-chip__text sort_elemet_data\" data-id=\"$id\">[$id] " . $this->translator->trans("classHelper_4") . "</div>
                    </div>
                </li>";
            }
        $html .= "</ul>";
        
        return $html;
    }
    
    public function createModuleSortListHtml($rows) {
        $html = "<ul class=\"sort_list\">";
            foreach ($rows as $key => $value) {
                $html .= "<li class=\"ui-state-default\">
                    <div class=\"mdc-chip\">
                        <i class=\"material-icons mdc-chip__icon mdc-chip__icon--leading\">drag_handle</i>
                        <div class=\"mdc-chip__text sort_elemet_data\" data-id=\"$key\">[$key] $value</div>
                    </div>
                </li>";
            }
            
            if ($this->session->get("moduleProfileId") == 0) {
                $moduleRows = $this->query->selectAllModuleDatabase();
                $id = count($moduleRows) + 1;
                
                $html .= "<li class=\"ui-state-default\">
                    <div class=\"mdc-chip\">
                        <i class=\"material-icons mdc-chip__icon mdc-chip__icon--leading\">drag_handle</i>
                        <div class=\"mdc-chip__text sort_elemet_data\" data-id=\"$id\">[$id] " . $this->translator->trans("classHelper_5") . "</div>
                    </div>
                </li>";
            }
        $html .= "</ul>";
        
        return $html;
    }
    
    public function createWordTagListHtml($rows) {
        $html = "";
        
        foreach ($rows as $key => $value) {
            if (isset($value['name']) == true) {
                $html .= "<div class=\"mdc-chip edit\">
                    <i class=\"material-icons mdc-chip__icon mdc-chip__icon--leading delete\">delete</i>
                    <div class=\"mdc-chip__text wordTag_elemet_data\" data-id=\"{$value['id']}\">{$value['name']}</div>
                </div>";
            }
        }
        
        return $html;
    }
    
    public function createTemplateList() {
        $templatesPath = "{$this->pathPublic}/images/templates";
        
        $scanDirElements = preg_grep("/^([^.])/", scandir($templatesPath));
        
        $list = Array();
        
        if ($scanDirElements != false) {
            foreach ($scanDirElements as $key => $value) {
                if ($value != "." && $value != ".." && is_dir("$templatesPath/$value") == true)
                    $list[$value] = $value;
            }
        }
        
        return $list;
    }
    
    public function createPageList($pageRows, $onlyMenuName, $pagination = null) {
        $pageListHierarchy = $this->createPageListHierarchy($pageRows, $pagination);
        
        if ($onlyMenuName == true) {
            $tag = "";
            $parentId = 0;
            $elements = Array();
            $count = 0;

            $pageListOnlyMenuName = $this->createPageListOnlyMenuName($pageListHierarchy, $tag, $parentId, $elements, $count);
            
            return $pageListOnlyMenuName;
        }
        
        return $pageListHierarchy;
    }
    
    public function assignUserPassword($type, $user, $form) {
        $row = $this->query->selectUserDatabase($user->getId());
        
        if ($type == "withOld") {
            if (password_verify($form->get("old")->getData(), $row['password']) == false)
                return $this->translator->trans("classHelper_1");
            else if ($form->get("new")->getData() != $form->get("newConfirm")->getData())
                return $this->translator->trans("classHelper_2");
            
            $user->setPassword($this->createPasswordEncoder($type, $user, $form));
        }
        else if ($type == "withoutOld") {
            if ($form->get("password")->getData() != "" || $form->get("passwordConfirm")->getData() != "") {
                if ($form->get("password")->getData() != $form->get("passwordConfirm")->getData())
                    return $this->translator->trans("classHelper_3");
                
                $user->setPassword($this->createPasswordEncoder($type, $user, $form));
            }
            else
                $user->setPassword($row['password']);
        }
        
        return "ok";
    }
    
    public function assignUserParameter($user) {
        $query = $this->connection->prepare("SELECT id FROM user
                                                ORDER BY id ASC LIMIT 1");
        
        $query->execute();
        
        $rowsCount = $query->rowCount();
        
        if ($rowsCount == 0) {
            $user->setRoleUserId("1,2,");
            $user->setRoles(Array("ROLE_USER", "ROLE_ADMIN"));
            $user->setCredit(0);
            $user->setActive(1);
        }
        else {
            $user->setRoleUserId("1,");
            $user->setRoles(Array("ROLE_USER"));
            $user->setCredit(0);
            $user->setActive(0);
        }
    }
    
    public function checkAttemptLogin($type, $userValue) {
        $row = $this->query->selectUserDatabase($userValue);
        
        $dateTimeCurrentLogin = new \DateTime($row['date_current_login']);
        $dateTimeCurrent = new \DateTime();
        
        $interval = intval($dateTimeCurrentLogin->diff($dateTimeCurrent)->format("%i"));
        $total = $this->settingRow['login_attempt_time'] - $interval;
        
        if ($total < 0)
            $total = 0;
        
        $dateCurrent = date("Y-m-d H:i:s");
        $dateLastLogin = strpos($row['date_last_login'], "0000") !== false ? $dateCurrent : $row['date_current_login'];
        
        $result = Array("", "");
        
        if (isset($row['id']) == true && $this->settingRow['login_attempt_time'] > 0) {
            $count = $row['attempt_login'] + 1;
            
            $query = $this->connection->prepare("UPDATE user
                                                    SET date_current_login = :dateCurrentLogin,
                                                        date_last_login = :dateLastLogin,
                                                        ip = :ip,
                                                        attempt_login = :attemptLogin
                                                    WHERE id = :id");
            
            if ($type == "success") {
                if ($count > $this->settingRow['login_attempt_count'] && $total > 0) {
                    $result[0] = "lock";
                    $result[1] = $total;
                    
                    return Array(false, $result[0], $result[1]);
                }
                else {
                    $query->bindValue(":dateCurrentLogin", $dateCurrent);
                    $query->bindValue(":dateLastLogin", $dateLastLogin);
                    $query->bindValue(":ip", $this->clientIp());
                    $query->bindValue(":attemptLogin", 0);
                    $query->bindValue(":id", $row['id']);

                    $query->execute();
                }
            }
            else if ($type == "failure") {
                if ($count > $this->settingRow['login_attempt_count'] && $total > 0) {
                    $result[0] = "lock";
                    $result[1] = $total;
                }
                else {
                    if ($count > $this->settingRow['login_attempt_count'])
                        $count = 1;
                    
                    $query->bindValue(":dateCurrentLogin", $dateCurrent);
                    $query->bindValue(":dateLastLogin", $row['date_last_login']);
                    $query->bindValue(":ip", $this->clientIp());
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
    
    public function checkUserActive($username) {
        $row = $this->query->selectUserDatabase($username);
        
        if ($row['active'] == false)
            return false;
        else
            return true;
    }
    
    public function checkUserRole($roleName, $user) {
        if ($user != null) {
            $row = $this->query->selectRoleUserDatabase($user->getRoleUserId());

            if ($this->arrayFindValue($roleName, $row) == true)
                return true;
        }
        
        return false;
    }
    
    public function sendMessageToSlackRoom($name, $text) {
        $row = $this->query->selectSettingSlackIwDatabase($name);
        
        if ($row != false) {
            $postFields = Array();
            $postFields['channel'] = $row['channel'];
            $postFields['text'] = $text;

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $row['hook']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));
            curl_setopt($curl, CURLOPT_HTTPHEADER, Array(
                "Content-Type: application/json"
            ));

            $curlResponse = curl_exec($curl);
            $curlError = curl_error($curl);
            $curlInfo = curl_getinfo($curl);
            
            curl_close($curl);
        }
    }
    
    public function sendMessageToLineChat($name, $text) {
        $row = $this->query->selectSettingLinePushDatabase($name);
        
        if ($row != false) {
            $postFields = Array();
            $postFields['to'] = $row['user_id'];
            $postFields['messages'] = Array(Array('type' => "text", 'text' => $text));
            
            $curl = curl_init();
            
            curl_setopt($curl, CURLOPT_URL, "https://api.line.me/v2/bot/message/push");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));
            curl_setopt($curl, CURLOPT_HTTPHEADER, Array(
                "Content-Type: application/json",
                "Authorization: Bearer {$row['access_token']}"
            ));
            
            $curlResponse = curl_exec($curl);
            $curlError = curl_error($curl);
            $curlInfo = curl_getinfo($curl);
            
            curl_close($curl);
        }
    }
    
    public function sendMessageToLineChatMultiple($name, $text) {
        $pushRow = $this->query->selectSettingLinePushDatabase($name);
        
        if ($pushRow != false) {
            $pushUserRows = $this->query->selectAllSettingLinePushUserDatabase("allPushName", $name);
            
            $to[] = $pushRow['user_id'];
            
            foreach ($pushUserRows as $key => $value) {
                if ($value['push_name'] == $name && $value['active'] == 1)
                    $to[] = $value['user_id'];
            }
            
            $to = array_unique($to);
            
            foreach ($to as $key => $value) {
                $postFields = Array();
                $postFields['to'] = $value;
                $postFields['messages'] = Array(Array('type' => "text", 'text' => $text));

                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, "https://api.line.me/v2/bot/message/push");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));
                curl_setopt($curl, CURLOPT_HTTPHEADER, Array(
                    "Content-Type: application/json",
                    "Authorization: Bearer {$pushRow['access_token']}"
                ));

                $curlResponse = curl_exec($curl);
                $curlError = curl_error($curl);
                $curlInfo = curl_getinfo($curl);
                
                curl_close($curl);
            }
        }
    }
    
    // ---
    
    public function xssProtection() {
        $nonceCsp = base64_encode(random_bytes(20));
        
        $this->session->set("xssProtectionTag", "Content-Security-Policy");
        $this->session->set("xssProtectionRule", "script-src 'strict-dynamic' 'nonce-{$nonceCsp}' 'unsafe-inline' http: https:; object-src 'none'; base-uri 'none';");
        $this->session->set("xssProtectionValue", $nonceCsp);
    }
    
    public function createCookie($name, $value, $expire, $secure, $httpOnly) {
        $currentCookieParams = session_get_cookie_params();
        
        if ($value == null)
            $value = isset($_COOKIE[$name]) == true ? $_COOKIE[$name] : $this->session->getId();
        
        setcookie($name, $value, $expire, $currentCookieParams['path'], $currentCookieParams['domain'], $secure, $httpOnly);
    }
    
    public function removeCookie($name) {
        if (isset($_COOKIE[$name]) == true) {
            $currentCookieParams = session_get_cookie_params();
            
            setcookie($name, null, time() - 3600, $currentCookieParams['path'], $currentCookieParams['domain'], false, false);
        }
    }
    
    public function sessionUnset() {
        $this->session->clear();
        
        $cookies = Array(
            $this->session->getName() . "_REMEMBERME"
        );
        
        foreach ($cookies as $value) {
            unset($_COOKIE[$value]);
        }
    }
    
    public function searchInFile($filePath, $word, $replace) {
        $reading = fopen($filePath, "r");
        $writing = fopen("{$filePath}.tmp", "w");
        
        $checked = false;
        
        while (feof($reading) == false) {
            $line = fgets($reading);
            
            if (stristr($line, $word) != false) {
                $line = $replace;
                
                $checked = true;
            }
            
            if (feof($reading) == true && $replace == null) {
                $line = "$word\n";

                $checked = true;
            }
            
            fwrite($writing, $line);
        }
        
        fclose($reading);
        fclose($writing);
        
        if ($checked == true) 
            rename("{$filePath}.tmp", $filePath);
        else
            unlink("{$filePath}.tmp");
    }
    
    public function removeDirRecursive($path, $parent) {
        if (file_exists($path) == true) {
            $rdi = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
            $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($rii as $file) {
                if (file_exists($file->getRealPath()) == true) {
                    if ($file->isDir() == true)
                        rmdir($file->getRealPath());
                    else
                        unlink($file->getRealPath());
                }
                else if (is_link($file->getPathName()) == true)
                    unlink($file->getPathName());
            }

            if ($parent == true)
                rmdir($path);
        }
    }
    
    public function generateRandomString($length) {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charactersLength = strlen($characters);
        $randomString = "";
        
        for ($a = 0; $a < $length; $a ++)
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        
        return $randomString;
    }
    
    public function sendEmail($to, $subject, $message, $from) {
        $headers  = "MIME-Version: 1.0 \r\n";
        $headers .= "Content-type: text/html; charset=UTF-8 \r\n";
        $headers .= "From: $from \r\n Reply-To: $from";

        mail($to, $subject, $message, $headers);
    }
    
    public function clientIp() {
        $ip = "";
        
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if(getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if(getenv("HTTP_X_FORWARDED"))
            $ip = getenv("HTTP_X_FORWARDED");
        else if(getenv("HTTP_FORWARDED_FOR"))
            $ip = getenv("HTTP_FORWARDED_FOR");
        else if(getenv("HTTP_FORWARDED"))
           $ip = getenv("HTTP_FORWARDED");
        else if(getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else
            $ip = "UNKNOWN";
        
        return $ip;
    }
    
    public function dateFormat($date) {
        $newData = Array("", "");
        
        $dateExplode = explode(" ", $date);
        
        if (count($dateExplode) == 0)
            $dateExplode = $newData;
        else {
            $sessionLanguageDate = $this->session->get("languageDate");
            
            $languageDate = $sessionLanguageDate == null ? "Y-m-d" : $sessionLanguageDate;
            
            if (strpos($dateExplode[0], "0000") === false)
                $dateExplode[0] = date($languageDate, strtotime($dateExplode[0]));
        }
        
        return $dateExplode;
    }
    
    public function timeFormat($type, $time) {
        if ($time == 0)
            return "0s";
        
        $result = Array();
        
        if ($type == "micro") {
            $elements = Array(
                'y' => $time / 31556926 % 12,
                'w' => $time / 604800 % 52,
                'd' => $time / 86400 % 7,
                'h' => $time / 3600 % 24,
                'm' => $time / 60 % 60,
                's' => $time % 60
            );
        }
        else if ($type == "seconds") {
            $elements = Array(
                'h' => floor($time / 3600),
                'm' => floor($time / 60),
                's' => $time % 60 == 0 ? round($time, 2) : $time % 60
            );
        }

        foreach ($elements as $key => $value) {
            if ($value > 0)
                $result[] = $value . $key;
        }

        return join(" ", $result);
    }
    
    public function unitFormat($value) {
        $result = "";
        
        if ($value == 0)
            $result = "0 Bytes";
        else {
            $reference = 1024;
            $sizes = Array("Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
            
            $index = floor(log($value) / log($reference));
            
            $result = round(floatval(($value / pow($reference, $index))), 2) . " " . $sizes[$index];
        }
        
        return $result;
    }
    
    public function cutStringOnLength($value, $length) {
        return strlen($value) > $length ? substr(value, 0, $length) . "..." : $value;
    }
    
    public function takeStringBetween($string, $start, $end) {
        $string = " " . $string;
        $position = strpos($string, $start);
        
        if ($position == 0)
            return "";
        
        $position += strlen($start);
        $length = strpos($string, $end, $position) - $position;
        
        return substr($string, $position, $length);
    }
    
    public function arrayLike($elements, $like) {
        $result = Array();
        
        foreach ($elements as $key => $value) {
            $result[$key] = preg_grep("~{$like}~i", $value);
            
            if (count($result[$key]) == 0)
                unset($result[$key]);
            else
                $result[$key] = $value;
        }
        
        return $result;
    }
    
    public function arrayMoveElement(&$array, $a, $b) {
        $out = array_splice($array, $a, 1);
        array_splice($array, $b, 0, $out);
    }
    
    public function arrayFindValue($elements, $subElements) {
        $result = false;
        
        foreach ($elements as $key => $value) {
            if (in_array($value, $subElements) == true) {
                $result = true;
                
                break;
            }
        }
        
        return $result;
    }
    
    public function arrayFindKeyWithValue($elements, $label, $item) {
        foreach ($elements as $key => $value) {
            if ($value[$label] == $item )
                return $key;
        }
        
        return false;
    }
    
    public function arrayExplodeFindValue($first, $second, $multi = true) {
        $firstExplode = explode(",", $first);
        array_pop($firstExplode);
        
        if ($multi == true) {
            $secondExplode =  explode(",", $second);
            array_pop($secondExplode);
            
            if ($this->arrayFindValue($firstExplode, $secondExplode) == true)
                return true;
        }
        else {
            if (in_array($second, $firstExplode) == true)
                return true;
        }
        
        return false;
    }
    
    public function arrayUniqueMulti($elements, $index, $fix = true) {
        $results = Array();
        
        $a = 0;
        $keys = Array();
        
        foreach ($elements as $key => $value) {
            if (in_array($value[$index], $keys) == false) {
                $results[$a] = $value;
                
                $keys[$a] = $value[$index];
            }
            
            $a ++;
        }
        
        if ($fix == true)
            $results = array_values($results);
        
        return $results;
    }
    
    public function arrayCombine($elementsA, $elementsB) {
        $count = min(count($elementsA), count($elementsB));
        
        return array_combine(array_slice($elementsA, 0, $count), array_slice($elementsB, 0, $count));
    }
    
    public function urlParameters($completeUrl, $baseUrl) {
        $lastPath = substr($completeUrl, strpos($completeUrl, $baseUrl) + strlen($baseUrl));
        $lastPathExplode = explode("/", $lastPath);
        array_shift($lastPathExplode);
        
        return $lastPathExplode;
    }
    
    public function requestParametersParse($parameters) {
        $result = Array();
        $matches = Array();
        
        foreach ($parameters as $key => $value) {
            if (is_object($value) == false)
                $result[$key] = $value;
            else {
                preg_match("#\[(.*?)\]#", $value->name, $matches);
                
                $keyTmp = "";
                
                if (count($matches) == 0)
                    $keyTmp = $value->name;
                else
                    $keyTmp = $matches[1];
                    
                $result[$keyTmp] = $value->value;
            }
        }
        
        return $result;
    }
    
    public function checkLanguage($request, $router) {
        $url = false;
        
        if ($this->session->get("languageTextCode") == null)
            $this->session->set("languageTextCode", $this->settingRow['language']);
        
        if ($request->get("languageTextCode") != null)
            $this->session->set("languageTextCode", $request->get("languageTextCode"));
        else if ($request->get("languageTextCode") == null && $request->get("_locale") != null)
            $this->session->set("languageTextCode", $request->get("_locale"));
        
        $request->setLocale($this->session->get("languageTextCode"));
        $request->setDefaultLocale($this->session->get("languageTextCode"));
        
        $languageRow = $this->query->selectLanguageDatabase($request->getLocale()); 
        
        if ($languageRow['active'] == false) {
            $request->setLocale($this->settingRow['language']);
            $request->setDefaultLocale($this->settingRow['language']);

            $url = $router->generate(
                "root_render",
                Array(
                    '_locale' => $request->getLocale(),
                    'urlCurrentPageId' => 2,
                    'urlExtra' => ""
                )
            );
        }
        
        return Array(
            $request,
            $url
        );
    }
    
    public function checkSessionOverTime($request, $router) {
        if (isset($_SESSION['currentUser']) == true) {
            $timeElapsed = time() - intval($this->session->get("userOvertime"));
            $userOverRole = false;
            
            if ($this->session->get("userOvertime") == null)
                $timeElapsed = 0;
            
            $currentUser = $this->tokenStorage->getToken()->getUser();
            
            if (is_string($currentUser) == false) {
                $userRow = $this->query->selectUserDatabase($currentUser->getId());
                
                $rolesExplode = explode(",", $userRow['roles']);
                
                $arrayDiff = array_diff($currentUser->getRoles(), $rolesExplode);
                
                if (count($arrayDiff) > 0) {
                    $userOverRole = true;
                    
                    $this->session->set("userInform", $this->translator->trans("classHelper_6"));
                }
            }
            
            if ($timeElapsed >= $this->sessionMaxIdleTime || $userOverRole == true) {
                $this->session->set("userInform", $this->translator->trans("classHelper_7"));
                
                if ($request->isXmlHttpRequest() == true) {
                    echo json_encode(Array(
                        'userInform' => $this->session->get("userInform")
                    ));
                    
                    exit;
                }
                else {
                    $this->session->remove("userOvertime");
                    
                    return $this->forceLogout($router);
                }
            }
            
            $this->session->set("userOvertime", time());
        }
        else {
            if ($this->session->get("forceLogout") == 2) {
                $this->session->set("userInform", "");
                
                $this->session->remove("forceLogout");
            }
            
            if ($this->session->get("userInform") != "")
                $this->session->set("forceLogout", 2);
        }
        
        return false;
    }
    
    public function forceLogout($router) {
        $this->session->set("forceLogout", 1);
        
        return $router->generate(
            "authentication_exit_check",
            Array(
                '_locale' => $this->session->get("languageTextCode"),
                'urlCurrentPageId' => 2,
                'urlExtra' => ""
            )
        );
    }
    
    public function checkHost($host) {
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $host);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.2309.372 Safari/537.36");
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        
        $curlResponse = curl_exec($curl);
        $curlError = curl_error($curl);
        $curlInfo = curl_getinfo($curl);
        
        curl_close($curl);
        
        if ($curlResponse == false)
            return false;
        
        return true;
    }
    
    public function replaceString4byte($string, $replacement, $remove = false) {
        $isFind = false;
        
        // A -> 1-3 | B -> 4-15 | C -> 16
        $newString = preg_replace("%(?:\xF0[\x90-\xBF][\x80-\xBF]{2} | [\xF1-\xF3][\x80-\xBF]{3} | \xF4[\x80-\x8F][\x80-\xBF]{2})%xs", $replacement, $string);    
        
        if (strpos($newString, $replacement) !== false)
            $isFind = $string;
        
        if ($remove == true)
            $newString = str_replace($replacement, "", $newString);
        
        return Array(
            $newString,
            $isFind
        );
    }
    
    public function download($path, $mime, $remove = false) {
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"" . basename($path) . "\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($path));
        header("Content-Type: {$mime}");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, pre-check=0, post-check=0");
        header("Pragma: public");

        readfile($path);

        if ($remove == true)
            unlink($path);
        
        return;
    }
    
    public function fileReadTail($path, $limit = 50) {
        $fopen = fopen($path, "r");
        
        fseek($fopen, -1, SEEK_END);
        
        for ($a = 0, $lines = Array(); $a < $limit && ($char = fgetc($fopen)) !== false;) {
            if ($char === "\n") {
                if (isset($lines[$a]) == true) {
                    $lines[$a][] = $char;
                    $lines[$a] = implode("", array_reverse($lines[$a]));
                    
                    $a ++;
                }
            }
            else
                $lines[$a][] = $char;
            
            fseek($fopen, -2, SEEK_CUR);
        }
        
        fclose($fopen);
        
        if (count($lines) > 0 && $a < $limit)
            $lines[$a] = implode("", array_reverse($lines[$a]));
        
        return array_reverse($lines);
    }
    
    public function loginAuthBasic($url, $username, $password) {
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.2309.372 Safari/537.36");
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
        
        $curlResponse = curl_exec($curl);
        $curlError = curl_error($curl);
        $curlInfo = curl_getinfo($curl);
        
        curl_close($curl);
        
        return $curlResponse;
    }
    
    public function closeAjaxRequest($response, $memoryLimit = false) {
        echo json_encode(Array(
            'response' => $response
        ));
        
        fastcgi_finish_request();
        ignore_user_abort(true);
        
        if ($memoryLimit == true) {
            set_time_limit(0);
            ini_set("memory_limit", "-1");
        }
    }
    
    public function escapeScript($value) {
        $pattern = "/<script.*?>|<\/script>|javascript:/i";
        $replacement = "";
        
        if (preg_match_all($pattern, $value, $matches) !== false)
            return preg_replace($pattern, $replacement, $value);
        else
            return $value;
    }
    
    // Functions private
    private function createPasswordEncoder($type, $user, $form) {
        if ($type == "withOld")
            return $this->passwordEncoder->encodePassword($user, $form->get("new")->getData());
        else if ($type == "withoutOld")
            return $this->passwordEncoder->encodePassword($user, $form->get("password")->getData());
    }
    
    private function createPageListHierarchy($pageRows, $pagination) {
        $elements = array_slice($pageRows, $pagination['offset'], $pagination['show']);
        
        $nodes = Array();
        $tree = Array();
        
        foreach ($elements as $page) {
            $nodes[$page['id']] = array_merge($page, Array(
                'children' => Array()
            ));
        }
        
        foreach ($nodes as &$node) {
            if ($node['parent'] == null || array_key_exists($node['parent'], $nodes) == false)
                $tree[] = &$node;
            else
                $nodes[$node['parent']]['children'][] = &$node;
        }
        
        unset($node);
        unset($nodes);
        
        return $tree;
    }
    
    private function createPageListOnlyMenuName($pageListHierarchy, &$tag, &$parentId, &$elements, &$count) {
        foreach ($pageListHierarchy as $key => $value) {
            if ($value['parent'] == null) {
                $count = 0;
                
                $tag = "-";
            }
            else if ($value['parent'] != null && $parentId != null && $value['parent'] < $parentId) {
                $count --;
                
                if ($count == 1)
                    $tag = substr($tag, 0, 2);
                else
                    $tag = substr($tag, 0, $count);
            }
            else if ($value['parent'] != null && $value['parent'] != $parentId) {
                $count ++;
                
                $tag .= "-";
            }
            
            $parentId = $value['parent'];
            
            $elements[$value['id']] = "|$tag| " . $value['alias'];
            
            if (count($value['children']) > 0)
                $this->createPageListOnlyMenuName($value['children'], $tag, $parentId, $elements, $count);
        }
        
        return $elements;
    }
    
    // ---
    
    private function arrayColumnFix() {
        if (function_exists("array_column") == false) {
            function array_column($input = null, $columnKey = null, $indexKey = null) {
                $argc = func_num_args();
                $params = func_get_args();
                
                if ($argc < 2) {
                    trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
                    
                    return null;
                }
                
                if (!is_array($params[0])) {
                    trigger_error("array_column() expects parameter 1 to be array, " . gettype($params[0]) . " given", E_USER_WARNING);
                    
                    return null;
                }
                
                if (!is_int($params[1]) && !is_float($params[1]) && !is_string($params[1]) && $params[1] !== null && !(is_object($params[1]) && method_exists($params[1], "__toString"))) {
                    trigger_error("array_column(): The column key should be either a string or an integer", E_USER_WARNING);
                    
                    return false;
                }
                
                if (isset($params[2]) && !is_int($params[2]) && !is_float($params[2]) && !is_string($params[2]) && !(is_object($params[2]) && method_exists($params[2], "__toString"))) {
                    trigger_error("array_column(): The index key should be either a string or an integer", E_USER_WARNING);
                    
                    return false;
                }
                
                $paramsInput = $params[0];
                $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
                $paramsIndexKey = null;
                
                if (isset($params[2])) {
                    if (is_float($params[2]) || is_int($params[2]))
                        $paramsIndexKey = (int)$params[2];
                    else
                        $paramsIndexKey = (string)$params[2];
                }
                
                $resultArray = array();
                
                foreach ($paramsInput as $row) {
                    $key = null;
                    $value = null;
                    
                    $keySet = false;
                    $valueSet = false;
                    
                    if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                        $keySet = true;
                        $key = (string)$row[$paramsIndexKey];
                    }
                    
                    if ($paramsColumnKey == null) {
                        $valueSet = true;
                        $value = $row;
                    }
                    else if (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                        $valueSet = true;
                        $value = $row[$paramsColumnKey];
                    }
                    
                    if ($valueSet) {
                        if ($keySet)
                            $resultArray[$key] = $value;
                        else
                            $resultArray[] = $value;
                    }
                }
                
                return $resultArray;
            }
        }
    }
}