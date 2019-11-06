<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="microservice_cron", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\MicroserviceCronRepository")
 * @UniqueEntity(fields={"name"}, groups={"microservice_cron_create", "microservice_cron_profile"})
 */
class MicroserviceCron {
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
     * @ORM\Column(name="time", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $time = "";
    
    /**
     * @ORM\Column(name="code", type="string", columnDefinition="longtext NOT NULL")
     */
    private $code = "";
    
    /**
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $active = false;
    
    /**
     * @ORM\Column(name="last_execution", type="string", columnDefinition="varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'")
     */
    private $lastExecution = "0000-00-00 00:00:00";
    
    // Properties
    public function setName($value) {
        $this->name = $value;
    }
    
    public function setTime($value) {
        $this->time = base64_encode($value);
    }
    
    public function setCode($value) {
        $this->code = base64_encode($value);
    }
    
    public function setActive($value) {
        $this->active = $value;
    }
    
    public function setLastExecution($value) {
        $this->lastExecution = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getTime() {
        return base64_decode($this->time);
    }
    
    public function getCode() {
        return base64_decode($this->code);
    }
    
    public function getActive() {
        return $this->active;
    }
    
    public function getLastExecution() {
        return $this->lastExecution;
    }
}