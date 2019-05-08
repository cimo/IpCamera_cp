<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="role_user", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\RoleUserRepository")
 * @UniqueEntity(fields={"level"}, groups={"roleUser_create", "roleUser_profile"})
 */
class RoleUser {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="level", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT 'ROLE_USER'")
     */
    private $level = "ROLE_USER";
    
    // Properties
    public function setLevel($value) {
        $this->level = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getLevel() {
        return $this->level;
    }
}