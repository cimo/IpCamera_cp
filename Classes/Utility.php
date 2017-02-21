<?php
require_once(dirname(__DIR__) . "/Config.php");
require_once("Database.php");
require_once("Query.php");

class Utility {
    // Vars
    private $config;
    private $database;
    private $query;
    
    private $settings;
    
    private $websiteName;
    
    private $pathRoot;
    private $pathRootFull;
    
    private $urlRoot;
    private $urlPublic;
    private $urlView;
    
    // Properties
    public function getQuery() {
        return $this->query;
    }
    
    public function getSettings() {
        return $this->settings;
    }
    
    public function getWebsiteName() {
        return $this->websiteName;
    }
    
    public function getPathRoot() {
        return $this->pathRoot;
    }
    
    public function getPathRootFull() {
        return $this->pathRootFull;
    }
    
    public function getUrlRoot() {
        return $this->urlRoot;
    }
    
    public function getUrlPublic() {
        return $this->urlPublic;
    }
    
    public function getUrlView() {
        return $this->urlView;
    }
    
    // Functions public
    public function __construct() {
        $this->config = new Config();
        $this->database = new Database();
        $this->query = new Query($this->database);
        
        $this->settings = $this->query->selectSettingsFromDatabase();
        
        $this->websiteName = $this->config->getName();
        
        $this->pathRoot = $this->config->getPathRoot();
        $this->pathRootFull = $_SERVER['DOCUMENT_ROOT'] . $this->pathRoot;
        
        $protocol = isset($_SERVER['HTTPS']) == true ? "https://" : "http://";
        $this->urlRoot = $protocol . $_SERVER['HTTP_HOST'] . $this->config->getUrlRoot() . $this->config->getFile();
        $this->urlPublic = $protocol . $_SERVER['HTTP_HOST'] . $this->config->getUrlRoot() . "/Resources/public";
        $this->urlView = $protocol . $_SERVER['HTTP_HOST'] . $this->config->getUrlRoot() . "/Resources/views";
        
        $this->arrayColumnFix();
    }
    
    public function generateToken() {
        $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(21));
    }
    
    public function checkSessionOverTime() {
        $sessionMaxIdleTime = 3600;
        
        if ($sessionMaxIdleTime > 0) {
            if (isset($_SESSION['LAST_ACTIVITY_TIME']) == true) {
                $timeLapse = time() - $_SESSION['LAST_ACTIVITY_TIME'];
                
                if ($timeLapse > $sessionMaxIdleTime) {
                    $this->sessionDestroy();
                    
                    return "Session time is over, please refresh the page.";
                }
            }
            
            $_SESSION['LAST_ACTIVITY_TIME'] = time();
        }
        
        return "";
    }
    
    public function sessionDestroy() {
        session_destroy();
        session_unset();
        
        $cookies = Array(
            'rememberme'
        );

        foreach ($cookies as $value)
            unset($_COOKIE[$value]);
    }
    
    public function configureCookie($name, $lifeTime, $secure, $httpOnly) {
        $currentCookieParams = session_get_cookie_params();
        
        $value = isset($_COOKIE[$name]) == true ? $_COOKIE[$name] : session_id();
        
        if (isset($_COOKIE[$name]) == true)
            setcookie($name, $value, $lifeTime, $currentCookieParams['path'], $currentCookieParams['domain'], $secure, $httpOnly);
    }
    
    public function searchInFile($filePath, $word, $replace = null) {
        $reading = fopen($filePath, "r");
        $writing = fopen($filePath + ".tmp", "w");
        
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
            @rename($filePath + ".tmp", $filePath);
        else
            @unlink($filePath + ".tmp");
    }
    
    public function removeDirRecursive($path, $parent = true) {
        if (file_exists($path) == true) {
            $rdi = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
            $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST);

            foreach($rii as $file) {
                if ($file->isDir() == true)
                    rmdir($file->getRealPath());
                else
                    @unlink($file->getRealPath());
            }

            if ($parent == true)
                rmdir($path);
        }
    }
    
    public function generateRandomString($length = 20) {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charactersLength = strlen($characters);
        $randomString = "";
        
        for ($i = 0; $i < $length; $i++)
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        
        return $randomString;
    }
    
    public function sendEmail($to, $subject, $message, $from) {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: $from\r\n" .
            "Reply-To: $from \r\n" .
            "X-Mailer: PHP/" . phpversion();

        mail($to, $subject, $message, $headers);
    }
    
    public function sizeUnits($bytes) {
        if ($bytes >= 1073741824)
            $bytes = number_format($bytes / 1073741824, 2) . " GB";
        else if ($bytes >= 1048576)
            $bytes = number_format($bytes / 1048576, 2) . " MB";
        else if ($bytes >= 1024)
            $bytes = number_format($bytes / 1024, 2) . " KB";
        else if ($bytes > 1)
            $bytes = $bytes . " bytes";
        else if ($bytes == 1)
            $bytes = $bytes . " byte";
        else
            $bytes = "0 bytes";

        return $bytes;
    }
    
    public function arrayLike($elements, $like, $flat = false) {
        $result = Array();
        
        if ($flat == true) {
            foreach($elements as $key => $value) {
                $pregGrep = preg_grep("~$like~i", $value);

                if (empty($pregGrep) === false)
                    $result[] = $elements[$key];
            }
        }
        else
            $result = preg_grep("~$like~i", $elements);
        
        return $result;
    }
    
    public function valueInSubArray($elements, $subElements) {
        $result = false;
        
        foreach($elements as $key => $value) {
            if (in_array($value, $subElements) == true) {
                $result = true;
                
                break;
            }
        }
        
        return $result;
    }
    
    // Functions private
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
                        $paramsIndexKey = (int) $params[2];
                    else
                        $paramsIndexKey = (string) $params[2];
                }
                
                $resultArray = array();
                
                foreach ($paramsInput as $row) {
                    $key = $value = null;
                    $keySet = $valueSet = false;
                    
                    if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                        $keySet = true;
                        $key = (string) $row[$paramsIndexKey];
                    }
                    
                    if ($paramsColumnKey === null) {
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