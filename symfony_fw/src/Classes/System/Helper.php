<?php
namespace App\Classes\System;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use App\Config;
use App\Classes\System\Query;
use App\Classes\System\QueryCustom;

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
    private $queryCustom;
    
    private $settingRow;
    
    private $languageFormat;
    
    private $protocol;
    
    private $pathRoot;
    private $pathSrc;
    private $pathPublic;
    
    private $urlRoot;
    
    private $supportSymlink;
    
    private $websiteFile;
    private $websiteName;

    private $sshConnection;
    private $sshSudo;
    
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
    
    public function getSessionMaxIdleTime() {
        return $this->sessionMaxIdleTime;
    }
    
    public function getQuery() {
        return $this->query;
    }
    
    public function getQueryCustom() {
        return $this->queryCustom;
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
    
    public function getPathLock() {
        return $this->pathLock;
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
        
        $this->sessionMaxIdleTime = ini_get("session.gc_maxlifetime");
        
        $this->config = new Config();
        $this->query = new Query($this);
        $this->queryCustom = new QueryCustom($this);
        
        $this->settingRow = $this->query->selectSettingDatabase();
        
        $languageRow = $this->query->selectLanguageDatabase($this->settingRow['language']);
        $this->languageFormat = $languageRow['date'];
        
        $serverRoot = isset($_SERVER['DOCUMENT_ROOT']) == true ? $_SERVER['DOCUMENT_ROOT'] : $this->settingRow['server_root'];
        $serverHost = isset($_SERVER['HTTP_HOST']) == true ? $_SERVER['HTTP_HOST'] : $this->settingRow['server_host'];
        
        $this->protocol = $this->config->getProtocol();
        
        $this->pathRoot = $serverRoot . $this->config->getPathRoot();
        $this->pathSrc = "{$this->pathRoot}/src";
        $this->pathPublic = "{$this->pathRoot}/public";
        $this->pathLock = "{$this->pathRoot}/src/files/lock";
        
        $this->urlRoot = $this->config->getProtocol() . $serverHost . $this->config->getUrlRoot();
        
        $this->supportSymlink = $this->config->getSupportSymlink();
        
        $this->websiteFile = $this->config->getFile();
        $this->websiteName = $this->config->getName();

        $this->sshConnection = false;
        $this->sshSudo = "";
        
        $this->arrayColumnFix();
    }
    
    public function createUserSelectHtml($selectId, $label, $isRequired = false) {
        $userRows = $this->query->selectAllUserDatabase();
        
        $required = $isRequired == true ? "required=\"required\"" : "";
        
        $html = "<div id=\"$selectId\" class=\"mdc-select\" $required>
            <select class=\"mdc-select__native-control\">
                <option value=\"\"></option>";
                foreach ($userRows as $key => $value) {
                    $html .= "<option value=\"{$value['id']}\">{$value['username']}</option>";
                }
            $html .= "</select>
            <label class=\"mdc-floating-label mdc-floating-label--float-above\">" . $this->translator->trans($label) . "</label>
            <div class=\"mdc-line-ripple\"></div>
        </div>";
        
        return $html;
    }
    
    public function createUserRoleSelectHtml($selectId, $label, $isRequired = false) {
        $roleUserRows = $this->query->selectAllRoleUserDatabase();
        
        $required = $isRequired == true ? "required=\"required\"" : "";
        
        $html = "<div id=\"$selectId\" class=\"mdc-select\" $required>
            <select class=\"mdc-select__native-control\">
                <option value=\"\"></option>";
                foreach ($roleUserRows as $key => $value) {
                    $html .= "<option value=\"{$value['id']}\">{$value['level']}</option>";
                }
            $html .= "</select>
            <label class=\"mdc-floating-label mdc-floating-label--float-above\">" . $this->translator->trans($label) . "</label>
            <div class=\"mdc-line-ripple\"></div>
        </div>";
        
        return $html;
    }
    
    public function createPageSelectHtml($language, $selectId, $label, $draft = false) {
        $pageRows = $this->query->selectAllPageDatabase($language, null, $draft);
        
        $pageList = $this->createPageList($pageRows, true);
        
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
        $languageRow = $this->query->selectLanguageDatabase($code);
        $languageRows = $this->query->selectAllLanguageDatabase();
        
        $key = array_search($languageRow, $languageRows);
        unset($languageRows[$key]);
        array_unshift($languageRows, $languageRow);
        
        $html = "";
        
        foreach ($languageRows as $key => $value) {
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
                        <div class=\"mdc-chip__text sort_elemet_data\" data-id=\"$id\">[$id] " . $this->translator->trans("classHelper_3") . "</div>
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
                        <div class=\"mdc-chip__text sort_elemet_data\" data-id=\"$id\">[$id] " . $this->translator->trans("classHelper_4") . "</div>
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
    
    public function assignUserPassword($user, $form) {
        $userRow = $this->query->selectUserDatabase($user->getId());
        
        if ($form->has("passwordOld") == true) {
            if ($this->passwordEncoder->isPasswordValid($user, $form->get("passwordOld")->getData()) == false)
                return $this->translator->trans("classHelper_1");
            else if ($form->get("password")->getData() != $form->get("passwordConfirm")->getData())
                return $this->translator->trans("classHelper_2");
            
            $user->setPassword($this->createPasswordEncoder($user, $form->get("password")->getData()));
        }
        else {
            if ($form->get("password")->getData() != "") {
                if ($form->get("password")->getData() != $form->get("passwordConfirm")->getData())
                    return $this->translator->trans("classHelper_2");
                
                $user->setPassword($this->createPasswordEncoder($user, $form->get("password")->getData()));
            }
            else
                $user->setPassword($userRow['password']);
        }
        
        return true;
    }
    
    public function assignUserParameter($user) {
        $firstRow = $this->query->selectFirstRowDatabase("user");
        
        if (count($firstRow) == 0) {
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
    
    public function checkAttemptLogin($type, $username) {
        $userRow = $this->query->selectUserDatabase($username);
        
        $dateTimeCurrentLogin = new \DateTime($userRow['date_current_login']);
        $dateTimeCurrent = new \DateTime();
        
        $difference = $this->settingRow['login_attempt_time'] - intval($dateTimeCurrentLogin->diff($dateTimeCurrent)->format("%i"));
        
        if ($difference < 0)
            $difference = 0;
        
        $dateCurrent = $this->dateFormat();
        $dateLast = strpos($userRow['date_last_login'], "0000") !== false ? $dateCurrent : $userRow['date_current_login'];
        
        if (isset($userRow['id']) == true) {
            if ($this->settingRow['login_attempt_time'] > 0) {
                $count = $userRow['attempt_login'] + 1;
                
                if ($difference > 0 && $count > $this->settingRow['login_attempt_count'])
                    return Array(false, "{$this->translator->trans("classHelper_7a")} {$this->settingRow['login_attempt_time']} {$this->translator->trans("classHelper_7b")}");
                
                if ($type == "success")
                    $this->query->updateUserDatabase("success", 0, $userRow['id'], $this->clientIp(), $dateCurrent, $dateLast, 0);
                else if ($type == "failure") {
                    $this->query->updateUserDatabase("failure", 0, $userRow['id'], $this->clientIp(), $dateCurrent, $dateLast, $count);
                    
                    return Array(false, "{$this->translator->trans("classHelper_8")} {$count} / {$this->settingRow['login_attempt_count']}");
                }
            }
        }
        else
            return Array(false, $this->translator->trans("classHelper_9"));
        
        return Array(true, "");
    }
    
    public function checkUserActive($username) {
        $userRow = $this->query->selectUserDatabase($username);
        
        if ($userRow['active'] == false)
            return Array(false, $this->translator->trans("classHelper_10"));
        
        return Array(true, "");
    }
    
    public function checkUserRole($roleName, $user) {
        if ($user != null) {
            $roleUserRow = $this->query->selectRoleUserDatabase($user->getRoleUserId());

            if ($this->arrayFindValue($roleName, $roleUserRow) == true)
                return true;
        }
        
        return false;
    }
    
    public function sendMessageToSlackRoom($name, $text) {
        $settingSlackIwRow = $this->query->selectSettingSlackIwDatabase($name);
        
        if ($settingSlackIwRow != false) {
            $postFields = Array();
            
            $postFields['channel'] = $settingSlackIwRow['channel'];
            $postFields['text'] = $text;

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $settingSlackIwRow['hook']);
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
        $settingLinePushRow = $this->query->selectSettingLinePushDatabase($name);
        
        if ($settingLinePushRow != false) {
            $postFields = Array();
            
            $postFields['to'] = $settingLinePushRow['user_id'];
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
                "Authorization: Bearer {$settingLinePushRow['access_token']}"
            ));
            
            $curlResponse = curl_exec($curl);
            $curlError = curl_error($curl);
            $curlInfo = curl_getinfo($curl);
            
            curl_close($curl);
        }
    }
    
    public function sendMessageToLineChatMultiple($name, $text) {
        $settingLinePushRow = $this->query->selectSettingLinePushDatabase($name);
        
        if ($settingLinePushRow != false) {
            $to[] = $settingLinePushRow['user_id'];
            
            $settingLinePushUserRows = $this->query->selectAllSettingLinePushUserDatabase("allPushName", $name);
            
            foreach ($settingLinePushUserRows as $key => $value) {
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
                    "Authorization: Bearer {$settingLinePushRow['access_token']}"
                ));

                $curlResponse = curl_exec($curl);
                $curlError = curl_error($curl);
                $curlInfo = curl_getinfo($curl);
                
                curl_close($curl);
            }
        }
    }
    
    public function xssProtection() {
        $nonceCsp = base64_encode(random_bytes(20));
        
        $this->session->set("xssProtectionTag", "Content-Security-Policy");
        $this->session->set("xssProtectionRule", "script-src 'strict-dynamic' 'nonce-{$nonceCsp}' 'unsafe-inline' http: https:; object-src 'none'; base-uri 'none';");
        $this->session->set("xssProtectionValue", $nonceCsp);
    }
    
    public function createCookie($name, $value, $expire, $secure, $httpOnly) {
        $currentCookieParams = session_get_cookie_params();
        
        if ($value == null)
            $value = isset($_COOKIE[$name]) == true ? $_COOKIE[$name] : 0;
        
        if ($expire == 0)
            $expire = time() + (10 * 365 * 24 * 60 * 60);
        else if ($expire == -1)
            $expire = time() - 3600;
        
        setcookie($name, $value, $expire, $currentCookieParams['path'], $currentCookieParams['domain'], $secure, $httpOnly);
    }
    
    public function removeCookie($name) {
        if (isset($_COOKIE[$name]) == true) {
            $this->createCookie($name, null, -1, false, false);
            
            unset($_COOKIE[$name]);
        }
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
    
    public function dateFormat($date = null, $time = true) {
        $newTime = $time == true ? "H:i:s" : "";
        $newDate = "{$this->languageFormat} {$newTime}";
        
        if ($date == null)
            $date = date($newDate);
        
        return date($newDate, strtotime($date));
    }
    
    public function timeFormat($type, $time) {
        if ($time == 0)
            return "0";
        
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
                preg_match('#\[(.*?)\]#', $value->name, $matches);
                
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
    
    public function checkLanguage($request) {
        $this->session->set("languageTextCode", $this->settingRow['language']);
        
        if ($request->get("languageTextCode") != null)
            $this->session->set("languageTextCode", $request->get("languageTextCode"));
        else if ($request->get("_locale") != null)
            $this->session->set("languageTextCode", $request->get("_locale"));
        
        $request->setLocale($this->session->get("languageTextCode"));
        $request->setDefaultLocale($this->session->get("languageTextCode"));
        
        return $request;
    }
    
    public function checkSessionOver($request, $router) {
        $currentUser = $this->session->get("currentUser");
        
        if ($currentUser != null) {
            $timeElapsed = time() - intval($this->session->get("userOvertime"));
            $userOverRole = false;
            
            if ($this->session->remove("userOvertime") == null)
                $timeElapsed = 0;
            
            $userRow = $this->query->selectUserDatabase($currentUser['id']);
            
            if ($currentUser['roles'] != $userRow['roles']) {
                $userOverRole = true;
                
                $this->session->set("userInform", $this->translator->trans("classHelper_5"));
            }
            
            if (($timeElapsed >= $this->sessionMaxIdleTime && $request->cookies->has("{$this->session->getName()}_remember_me") == false) || $userOverRole == true) {
                $this->createCookie("{$this->session->getName()}_sessionOver", 1, 0, true, false);
                
                if ($userOverRole == false)
                    $this->session->set("userInform", $this->translator->trans("classHelper_6"));
                
                if ($request->isXmlHttpRequest() == true) {
                    echo json_encode(Array(
                        'userInform' => $this->session->get("userInform"),
                        'sessionOver' => true
                    ));
                    
                    $this->removeCookie("{$this->session->getName()}_login");
                    
                    exit;
                }
                else
                    return $this->forceLogout($router);
            }
            
            $this->session->set("userOvertime", time());
        }
        else {
            if ($request->cookies->has("{$this->session->getName()}_sessionOver") == true) {
                $this->removeCookie("{$this->session->getName()}_sessionOver");
                
                $this->session->set("userInform", $this->translator->trans("classHelper_6"));
                
                if ($request->isXmlHttpRequest() == true) {
                    echo json_encode(Array(
                        'userInform' => $this->session->get("userInform"),
                        'sessionOver' => true
                    ));
                    
                    $this->removeCookie("{$this->session->getName()}_login");
                    
                    exit;
                }
            }
            else
                $this->session->set("userInform", "");
        }
        
        return false;
    }
    
    public function forceLogout($router) {
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
        
        if ($curlResponse === false)
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
    
    public function fileSearchInside($filePath, $word, $replace) {
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
    
    public function writeLog($path, $name, $message, $elements = null) {
        $logPath = "{$path}/" . str_replace(" ", "_", $name) . ".log";
        
        file_put_contents($logPath, "{$this->dateFormat()} - IP[{$_SERVER['REMOTE_ADDR']}]: {$message}", FILE_APPEND);
        
        if ($elements != null && (is_array($elements) == true || is_object($elements) == true))
            file_put_contents($logPath, print_r($elements, true), FILE_APPEND);
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
    
    public function createProcessLock($name) {
        $path = "{$this->pathLock}/{$name}_lock";
        
        if (file_exists($path) == false) {
            $this->session->set("processLockPath", $path);
            
            file_put_contents($this->session->get("processLockPath"), "");
            
            return true;
        }
        
        return false;
    }
    
    public function responseProcessLock($response) {
        $response['messages']['error'] = $this->translator->trans("process_lock_1");
        
        return $response;
    }
    
    public function removeProcessLock() {
        $path = $this->session->get("processLockPath");
        
        if (file_exists($path) == true)
            unlink($path);
        
        $this->session->remove("processLockPath");
    }

    public function sshConnection($ip, $port, $username, $options = Array()) {
        $this->sshConnection = @ssh2_connect($ip, $port);

        if ($this->sshConnection == false)
            return false;

        if (count($options) > 1) {
            $auth = @ssh2_auth_pubkey_file($this->sshConnection, $username, $options[0], $options[1], $options[2]);

            $this->sshSudo = "sudo";
        }
        else if (count($options) == 1) {
            $auth = @ssh2_auth_password($this->sshConnection, $username, $options[0]);

            $this->sshSudo = "echo '{$options[0]}' | sudo -S";
        }
        else
            return false;

        if ($auth == false)
            return false;

        return true;
    }

    public function sshExecution($commands) {
        if ($this->sshConnection == false || $this->sshSudo == "")
            return false;

        $result = "";

        $command = implode(";", $commands);
        $command = str_replace("sudo ", "{$this->sshSudo} ", $command);

        $stream = ssh2_exec($this->sshConnection, $command);

        stream_set_blocking($stream, true);

        $dio_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
        $err_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

        stream_set_blocking($dio_stream, true);
        stream_set_blocking($err_stream, true);

        $result .= stream_get_contents($dio_stream) . "\r\n";
        $result .= stream_get_contents($err_stream) . "\r\n";

        fclose($stream);

        return $result;
    }

    // Functions private
    private function createPasswordEncoder($user, $password) {
        return $this->passwordEncoder->encodePassword($user, $password);
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
                
                $inputs = $params[0];
                $columnKeys = ($params[1] !== null) ? (string) $params[1] : null;
                $indexKeys = null;
                
                if (isset($params[2])) {
                    if (is_float($params[2]) || is_int($params[2]))
                        $indexKeys = (int)$params[2];
                    else
                        $indexKeys = (string)$params[2];
                }
                
                $resultArray = array();
                
                foreach ($inputs as $key => $value) {
                    $key = null;
                    $value = null;
                    
                    $keySet = false;
                    $valueSet = false;
                    
                    if ($indexKeys !== null && array_key_exists($indexKeys, $value)) {
                        $keySet = true;
                        $key = (string) $value[$indexKeys];
                    }
                    
                    if ($columnKeys == null) {
                        $valueSet = true;
                        $value = $value;
                    }
                    else if (is_array($value) && array_key_exists($columnKeys, $value)) {
                        $valueSet = true;
                        $value = $value[$columnKeys];
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