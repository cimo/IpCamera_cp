<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="languages", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\LanguageRepository")
 */
class Language {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="code", type="string", columnDefinition="varchar(2) NOT NULL DEFAULT ''")
     */
    private $code = "";
    
    /**
     * @ORM\Column(name="date", type="string", columnDefinition="varchar(5) NOT NULL DEFAULT 'Y-m-d'")
     */
    private $date = "Y-m-d";
    
    /**
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $active = 0;
    
    // Properties
    public function setCode($value) {
        $this->code = $value;
    }
    
    public function setDate($value) {
        $this->date = $value;
    }
    
    public function setActive($value) {
        $this->active = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getCode() {
        return $this->code;
    }
    
    public function getDate() {
        return $this->date;
    }
    
    public function getActive() {
        return $this->active;
    }
}