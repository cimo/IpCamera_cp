<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="ipCamera_device", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\IpCameraRepository")
 * @UniqueEntity(fields={"name"}, groups={"ipCamera_create", "ipCamera_profile"})
 */
class IpCamera {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="name", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $name = "";
    
    /**
     * @ORM\Column(name="user_id", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT '1,'")
     */
    private $userId = "1,";
    
    /**
     * @ORM\Column(name="host_video", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $hostVideo = "";
    
    /**
     * @ORM\Column(name="host_image", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $hostImage = "";
    
    /**
     * @ORM\Column(name="username", type="string", columnDefinition="varchar(20) NOT NULL DEFAULT ''")
     */
    private $username = "";
    
    /**
     * @ORM\Column(name="password", type="string", nullable=true, columnDefinition="blob")
     */
    private $password = "";
    
    /**
     * @ORM\Column(name="detection_sensitivity", type="decimal", columnDefinition="decimal(4,3) NOT NULL DEFAULT 0.01")
     */
    private $detectionSensitivity = 0.01;
    
    /**
     * @ORM\Column(name="detection_active", type="integer", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $detectionActive = false;
    
    /**
     * @ORM\Column(name="active", type="integer", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $active = false;
    
    // Properties
    public function setId($value) {
        $this->id = $value;
    }
    
    public function setName($value) {
        $this->name = $value;
    }
    
    public function setUserId($value) {
        $this->userId = $value;
    }
    
    public function setHostVideo($value) {
        $this->hostVideo = $value;
    }
    
    public function setHostImage($value) {
        $this->hostImage = $value;
    }
    
    public function setUsername($value) {
        $this->username = $value;
    }
    
    public function setPassword($value) {
        $this->password = $value;
    }
    
    public function setDetectionSensitivity($value) {
        $this->detectionSensitivity = $value + 0;
    }
    
    public function setDetectionActive($value) {
        $this->detectionActive = $value;
    }
    
    public function setActive($value) {
        $this->active = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getUserId() {
        return $this->userId;
    }
    
    public function getHostVideo() {
        return $this->hostVideo;
    }
    
    public function getHostImage() {
        return $this->hostImage;
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function getPassword() {
        return $this->password;
    }
    
    public function getDetectionSensitivity() {
        return $this->detectionSensitivity + 0;
    }
    
    public function getDetectionActive() {
        return $this->detectionActive;
    }
    
    public function getActive() {
        return $this->active;
    }
}