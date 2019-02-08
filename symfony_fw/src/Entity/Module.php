<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ModuleRepository")
 * @ORM\Table(name="modules", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @UniqueEntity(fields={"name"}, groups={"module_create", "module_profile"})
 */
class Module {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="position", type="string", columnDefinition="varchar(6) NOT NULL DEFAULT 'center'")
     */
    private $position = "center";
    
    /**
     * @ORM\Column(name="position_tmp", type="string", nullable=true, columnDefinition="varchar(6)")
     */
    private $positionTmp = null;
    
    /**
     * @ORM\Column(name="rank_in_column", type="integer", nullable=true, columnDefinition="int(11)")
     */
    private $rankInColumn = null;
    
    // #
    private $rankColumnSort = null;
    
    /**
     * @ORM\Column(name="name", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $name = "";
    
    /**
     * @ORM\Column(name="label", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $label = null;
    
    /**
     * @ORM\Column(name="controller_name", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $controllerName = "";
    
    /**
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $active = false;

    // Properties
    public function setPosition($value) {
        $this->position = $value;
    }
    
    public function setPositionTmp($value) {
        $this->positionTmp = $value;
    }
    
    public function setRankInColumn($value) {
        $this->rankInColumn = $value;
    }
    
    public function setRankColumnSort($value) {
        $this->rankColumnSort = $value;
    }
    
    public function setName($value) {
        $this->name = $value;
    }
    
    public function setLabel($value) {
        $this->label = $value;
    }
    
    public function setControllerName($value) {
        $this->controllerName = $value;
    }
    
    public function setActive($value) {
        $this->active = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getPosition() {
        return $this->position;
    }
    
    public function getPositionTmp() {
        return $this->positionTmp;
    }
    
    public function getRankInColumn() {
        return $this->rankInColumn;
    }
    
    public function getRankColumnSort() {
        return $this->rankColumnSort;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getLabel() {
        return $this->label;
    }
    
    public function getControllerName() {
        return $this->controllerName;
    }
    
    public function getActive() {
        return $this->active;
    }
}