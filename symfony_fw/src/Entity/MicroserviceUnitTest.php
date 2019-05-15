<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="microservice_unit_test", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\MicroserviceUnitTestRepository")
 * @UniqueEntity(fields={"name"}, groups={"microservice_unit_test_create", "microservice_unit_test_profile"})
 */
class MicroserviceUnitTest {
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
     * @ORM\Column(name="origin", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $origin = "";
    
    /**
     * @ORM\Column(name="code", type="string", columnDefinition="longtext NOT NULL")
     */
    private $code = "";
    
    /**
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $active = false;
    
    // Properties
    public function setName($value) {
        $this->name = $value;
    }
    
    public function setOrigin($value) {
        $this->origin = $value;
    }
    
    public function setCode($value) {
        $this->code = base64_encode($value);
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
    
    public function getOrigin() {
        return $this->origin;
    }
    
    public function getCode() {
        return base64_decode($this->code);
    }
    
    public function getActive() {
        return $this->active;
    }
}