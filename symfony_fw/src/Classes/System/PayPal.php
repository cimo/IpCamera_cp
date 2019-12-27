<?php
namespace App\Classes\System;

class PayPal {
    // Vars
    private $debug;
    private $certificate;
    private $sandbox;
    
    private $elements;
    
    // Properties
    public function getElements() {
        return $this->elements;
    }
    
    // Functions public
    public function __construct($debug, $certificate, $sandbox) {
        $this->debug = $debug;
        $this->certificate = $certificate;
        $this->sandbox = $sandbox;
        
        $this->elements = Array();
    }
    
    public function ipn() {
        $content = file_get_contents("php://input");
        $contentExplode = explode("&", $content);
        
        foreach ($contentExplode as $value) {
            $valueExplode = explode("=", $value);

            if (count($valueExplode) == 2) {
                if ($valueExplode[0] == "payment_date") {
                    if (substr_count($valueExplode[1], "+") == 1)
                        $valueExplode[1] = str_replace("+", "%2B", $valueExplode[1]);
                }
                
                $this->elements[$valueExplode[0]] = urldecode($valueExplode[1]);
            }
        }
        
        $postFields = "cmd=_notify-validate";
        
        if (function_exists("get_magic_quotes_gpc") == true)
            $getMagicQuotesExists = true;
        
        foreach ($this->elements as $key => $value) {
            if ($getMagicQuotesExists == true && get_magic_quotes_gpc() == 1)
                $value = urlencode(stripslashes($value));
            else
                $value = urlencode($value);
            
            $postFields .= "&$key=$value";
        }
        
        if ($this->sandbox == true)
            $payPalUrl = "https://ipnpb.sandbox.paypal.com/cgi-bin/webscr";
        else
            $payPalUrl = "https://ipnpb.paypal.com/cgi-bin/webscr";
        
        $curl = curl_init($payPalUrl);
        
        if ($curl == FALSE)
            return false;
        
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($curl, CURLOPT_HTTPHEADER, Array(
            "Connection: Close"
        ));
        
        if ($this->certificate == true)
            curl_setopt($curl, CURLOPT_CAINFO, dirname(__DIR__) . "/files/paypal.pem");
        
        if($this->debug == true) {
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        }
        
        curl_setopt($curl, CURLOPT_SSLVERSION, 6);
        curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
        
        $curlResponse = trim(end(preg_split("/^\r?$/m", curl_exec($curl))));
        $curlInfo = curl_getinfo($curl);
        
        if($this->debug == true) {
            if ($curlInfo['http_code'] != 200) {
                error_log(date("Y-m-d H:i:s e") . " - PayPal responded with http code: " . print_r($curlInfo['http_code'], true) . PHP_EOL);
                
                return false;
            }
        }
        
        curl_close($curl);
        
        if ($curlResponse == "VERIFIED") {
            if ($this->debug == true)
                error_log(date("Y-m-d H:i:s e") . " - Verified IPN: " . print_r($postFields, true) . PHP_EOL);
            
            return true;
        }
        
        return false;
    }

    // Functions private
}