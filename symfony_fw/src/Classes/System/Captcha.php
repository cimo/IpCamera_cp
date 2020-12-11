<?php
namespace App\Classes\System;

class Captcha {
    // Vars
    private $helper;
    
    private $session;
    
    // Properties
    
    // Functions public
    public function __construct($helper) {
        $this->helper = $helper;
        
        $this->session = $this->helper->getSession();
    }
    
    public function create($length) {
        $randomString = $this->helper->generateRandomString($length);
        
        $this->session->set("captcha", $randomString);
        
        return $this->image($this->session->get("captcha"));
    }
    
    // Functions private
    private function image($string) {
        $image = imagecreatetruecolor(80, 30);
        $red = imagecolorallocate($image, 0xFF, 0x00, 0x00);
        $black = imagecolorallocate($image, 0x00, 0x00, 0x00);
        
        imagefilledrectangle($image, 0, 0, 299, 99, $red);
        
        $fontFile = dirname($_SERVER['DOCUMENT_ROOT']) . $this->helper->getPathRoot() . "/public/fonts/roboto_light.ttf";
        
        imagefttext($image, 10, 0, 12, 20, $black, $fontFile, $string);
        
        ob_start();
        header("Content-type: image/png");
        imagepng($image);
        $result = base64_encode(ob_get_contents());
        ob_end_clean();
        
        imagedestroy($image);
        
        return $result;
    }
    
    public function check($captchaEnabled, $captcha) {
        $sessionCaptcha = $this->session->get("captcha");
        
        if ($captchaEnabled == false || ($captchaEnabled == true && $sessionCaptcha != null && $sessionCaptcha == $captcha))
            return Array(true, "");
        
        return Array(false, $this->helper->getTranslator()->trans("captcha_1"));
    }
}