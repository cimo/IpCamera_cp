<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="setting_slack_iw", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\SettingSlackIwRepository")
 * @UniqueEntity(fields={"name"}, groups={"setting_slack_iw"})
 */
class SettingSlackIw {
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
     * @ORM\Column(name="hook", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $hook = "";
    
    /**
     * @ORM\Column(name="channel", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $channel = "";
    
    /**
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $active = "";
    
    // Properties
    public function setName($value) {
        $this->name = $value;
    }
    
    public function setHook($value) {
        $this->hook = $value;
    }
    
    public function setChannel($value) {
        $this->channel = $value;
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
    
    public function getHook() {
        return $this->hook;
    }
    
    public function getChannel() {
        return $this->channel;
    }
    
    public function getActive() {
        return $this->active;
    }
}