<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="ipCamera_devices", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
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
     * @ORM\Column(name="host", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $host = "";
    
    /**
     * @ORM\Column(name="username", type="string", columnDefinition="varchar(20) NOT NULL DEFAULT ''")
     */
    private $username = "";
    
    /**
     * @ORM\Column(name="password", type="string", type="string", nullable=true, columnDefinition="blob")
     */
    private $password = "";
    
    /**
     * @ORM\Column(name="detection_sensitivity", type="decimal", columnDefinition="decimal(4,3) NOT NULL DEFAULT 0.1")
     */
    private $detectionSensitivity = "";
    
    /**
     * @ORM\Column(name="active", type="integer", type="string", nullable=true, columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $active = "";
    
    // Properties
    public function setName($value) {
        $this->name = $value;
    }
    
    public function setHost($value) {
        $this->host = $value;
    }
    
    public function setUsername($value) {
        $this->username = $value;
    }
    
    public function setPassword($value) {
        $this->password = $value;
    }
    
    public function setDetectionSensitivity($value) {
        $this->detectionSensitivity = $value;
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
    
    public function getHost() {
        return $this->host;
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function getPassword() {
        return $this->password;
    }
    
    public function getDetectionSensitivity() {
        return $this->detectionSensitivity;
    }
    
    public function getActive() {
        return $this->active;
    }
}