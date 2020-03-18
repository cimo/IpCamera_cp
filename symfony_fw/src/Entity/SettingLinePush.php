<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="setting_line_push", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\SettingLinePushRepository")
 * @UniqueEntity(fields={"name"}, groups={"setting_line_push"})
 */
class SettingLinePush {
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
     * @ORM\Column(name="user_id_primary", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $userIdPrimary = "";
    
    /**
     * @ORM\Column(name="access_token", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $accessToken = "";
    
    /**
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $active = false;
    
    // Properties
    public function setId($value) {
        $this->id = $value;
    }
    
    public function setName($value) {
        $this->name = $value;
    }
    
    public function setUserIdPrimary($value) {
        $this->userIdPrimary = $value;
    }
    
    public function setAccessToken($value) {
        $this->accessToken = $value;
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
    
    public function getUserIdPrimary() {
        return $this->userIdPrimary;
    }
    
    public function getAccessToken() {
        return $this->accessToken;
    }
    
    public function getActive() {
        return $this->active;
    }
}