<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="microservice_apiBasic", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\ApiBasicRepository")
 * @UniqueEntity(fields={"name"}, groups={"apiBasic_create", "apiBasic_profile"})
 * @UniqueEntity(fields={"tokenName"}, groups={"apiBasic_create", "apiBasic_profile"})
 */
class ApiBasic {
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
     * @ORM\Column(name="token_name", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $tokenName = "";
    
    /**
     * @ORM\Column(name="ip", type="string", nullable=true, columnDefinition="longtext")
     */
    private $ip = "";
    
    /**
     * @ORM\Column(name="url_callback", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $urlCallback = "";
    
    /**
     * @ORM\Column(name="database_ip", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $databaseIp = "";
    
    /**
     * @ORM\Column(name="database_name", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $databaseName = "";
    
    /**
     * @ORM\Column(name="database_username", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $databaseUsername = "";
    
    /**
     * @ORM\Column(name="database_password", type="string", nullable=true, columnDefinition="blob")
     */
    private $databasePassword = "";
    
    /**
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $active = false;
    
    /**
     * @ORM\Column(name="slack_active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $slackActive = false;
    
    /**
     * @ORM\Column(name="line_active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $lineActive = false;
    
    // Properties
    public function setName($value) {
        $this->name = $value;
    }
    
    public function setTokenName($value) {
        $this->tokenName = $value;
    }
    
    public function setIp($value) {
        $this->ip = $value;
    }
    
    public function setUrlCallback($value) {
        $this->urlCallback = $value;
    }
    
    public function setDatabaseIp($value) {
        $this->databaseIp = $value;
    }
    
    public function setDatabaseName($value) {
        $this->databaseName = $value;
    }
    
    public function setDatabaseUsername($value) {
        $this->databaseUsername = $value;
    }
    
    public function setDatabasePassword($value) {
        $this->databasePassword = $value;
    }
    
    public function setActive($value) {
        $this->active = $value;
    }
    
    public function setSlackActive($value) {
        $this->slackActive = $value;
    }
    
    public function setLineActive($value) {
        $this->lineActive = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getTokenName() {
        return $this->tokenName;
    }
    
    public function getIp() {
        return $this->ip;
    }
    
    public function getUrlCallback() {
        return $this->urlCallback;
    }
    
    public function getDatabaseIp() {
        return $this->databaseIp;
    }
    
    public function getDatabaseName() {
        return $this->databaseName;
    }
    
    public function getDatabaseUsername() {
        return $this->databaseUsername;
    }
    
    public function getDatabasePassword() {
        return $this->databasePassword;
    }
    
    public function getActive() {
        return $this->active;
    }
    
    public function getSlackActive() {
        return $this->slackActive;
    }
    
    public function getLineActive() {
        return $this->lineActive;
    }
}