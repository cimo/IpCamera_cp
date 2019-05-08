<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="page_comment", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\PageCommentRepository")
 */
class PageComment {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="page_id", type="integer", columnDefinition="int(11) NOT NULL DEFAULT 0")
     */
    private $pageId = 0;
    
    /**
     * @ORM\Column(name="username", type="string", columnDefinition="varchar(20) NOT NULL DEFAULT ''")
     */
    private $username = "";
    
    /**
     * @ORM\Column(name="id_reply", type="integer", nullable=true, columnDefinition="int(11)")
     */
    private $idReply = null;
    
    /**
     * @ORM\Column(name="argument", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $argument = "";
    
    /**
     * @ORM\Column(name="date_create", type="string", columnDefinition="varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateCreate = "0000-00-00 00:00:00";
    
    /**
     * @ORM\Column(name="date_modify", type="string", columnDefinition="varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateModify = "0000-00-00 00:00:00";
    
    // #
    private $type = "";
    
    // Properties
    public function setPageId($value) {
        $this->pageId = $value;
    }
    
    public function setUsername($value) {
        $this->username = $value;
    }
    
    public function setIdReply($value) {
        $this->idReply = $value;
    }
    
    public function setArgument($value) {
        $this->argument = $value;
    }
    
    public function setDateCreate($value) {
        $this->dateCreate = $value;
    }
    
    public function setDateModify($value) {
        $this->dateModify = $value;
    }
    
    public function setType($value) {
        $this->type = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getPageId() {
        return $this->pageId;
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function getIdReply() {
        return $this->idReply;
    }
    
    public function getArgument() {
        return $this->argument;
    }
    
    public function getDateCreate() {
        return $this->dateCreate;
    }
    
    public function getDateModify() {
        return $this->dateModify;
    }
    
    public function getType() {
        return $this->type;
    }
}