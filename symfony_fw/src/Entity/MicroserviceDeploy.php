<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="microservice_deploy", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\MicroserviceDeployRepository")
 * @UniqueEntity(fields={"name"}, groups={"microservice_deploy_create", "microservice_deploy_profile"})
 */
class MicroserviceDeploy {
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
     * @ORM\Column(name="description", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $description = "";
    
    /**
     * @ORM\Column(name="system_user", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $systemUser = "";
    
    /**
     * @ORM\Column(name="ssh_username", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $sshUsername = "";
    
    /**
     * @ORM\Column(name="ssh_password", type="string", nullable=true, columnDefinition="blob")
     */
    private $sshPassword = "";
    
    /**
     * @ORM\Column(name="key_public", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $keyPublic = "";
    
    private $removeKeyPublic = false;
    
    /**
     * @ORM\Column(name="key_private", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $keyPrivate = "";
    
    private $removeKeyPrivate = false;
    
    /**
     * @ORM\Column(name="key_private_password", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $keyPrivatePassword = "";
    
    /**
     * @ORM\Column(name="ip", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $ip = "";
    
    /**
     * @ORM\Column(name="git_user_email", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $gitUserEmail = "";
    
    /**
     * @ORM\Column(name="git_user_name", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $gitUserName = "";
    
    /**
     * @ORM\Column(name="git_clone_url", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $gitCloneUrl = "";
    
    /**
     * @ORM\Column(name="git_clone_url_username", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $gitCloneUrlUsername = "";
    
    /**
     * @ORM\Column(name="git_clone_url_password", type="string", nullable=true, columnDefinition="blob")
     */
    private $gitCloneUrlPassword = "";
    
    /**
     * @ORM\Column(name="git_clone_path", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $gitClonePath = "";
    
    /**
     * @ORM\Column(name="user_git_script", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $userGitScript = "";
    
    /**
     * @ORM\Column(name="user_web_script", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $userWebScript = "";
    
    /**
     * @ORM\Column(name="root_web_path", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $rootWebPath = "";
    
    /**
     * @ORM\Column(name="command", type="string", nullable=true, columnDefinition="longtext")
     */
    private $command = "";
    
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
    
    public function setDescription($value) {
        $this->description = $value;
    }
    
    public function setSystemUser($value) {
        $this->systemUser = $value;
    }
    
    public function setSshUsername($value) {
        $this->sshUsername = $value;
    }
    
    public function setSshPassword($value) {
        $this->sshPassword = $value;
    }
    
    public function setKeyPublic($value) {
        $this->keyPublic = $value;
    }
    
    public function setRemoveKeyPublic($value) {
        $this->removeKeyPublic = $value;
    }
    
    public function setKeyPrivate($value) {
        $this->keyPrivate = $value;
    }
    
    public function setRemoveKeyPrivate($value) {
        $this->removeKeyPrivate = $value;
    }
    
    public function setKeyPrivatePassword($value) {
        $this->keyPrivatePassword = $value;
    }
    
    public function setIp($value) {
        $this->ip = $value;
    }
    
    public function setGitUserEmail($value) {
        $this->gitUserEmail = $value;
    }
    
    public function setGitUserName($value) {
        $this->gitUserName = $value;
    }
    
    public function setGitCloneUrl($value) {
        $this->gitCloneUrl = $value;
    }
    
    public function setGitCloneUrlUsername($value) {
        $this->gitCloneUrlUsername = $value;
    }
    
    public function setGitCloneUrlPassword($value) {
        $this->gitCloneUrlPassword = $value;
    }
    
    public function setGitClonePath($value) {
        $this->gitClonePath = $value;
    }
    
    public function setUserGitScript($value) {
        $this->userGitScript = $value;
    }
    
    public function setUserWebScript($value) {
        $this->userWebScript = $value;
    }
    
    public function setRootWebPath($value) {
        $this->rootWebPath = $value;
    }
    
    public function setCommand($value) {
        $this->command = $value;
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
    
    public function getDescription() {
        return $this->description;
    }
    
    public function getSystemUser() {
        return $this->systemUser;
    }
    
    public function getSshUsername() {
        return $this->sshUsername;
    }
    
    public function getSshPassword() {
        return $this->sshPassword;
    }
    
    public function getKeyPublic() {
        return $this->keyPublic;
    }
    
    public function getRemoveKeyPublic() {
        return $this->removeKeyPublic;
    }
    
    public function getKeyPrivate() {
        return $this->keyPrivate;
    }
    
    public function getRemoveKeyPrivate() {
        return $this->removeKeyPrivate;
    }
    
    public function getKeyPrivatePassword() {
        return $this->keyPrivatePassword;
    }
    
    public function getIp() {
        return $this->ip;
    }
    
    public function getGitUserEmail() {
        return $this->gitUserEmail;
    }
    
    public function getGitUserName() {
        return $this->gitUserName;
    }
    
    public function getGitCloneUrl() {
        return $this->gitCloneUrl;
    }
    
    public function getGitCloneUrlUsername() {
        return $this->gitCloneUrlUsername;
    }
    
    public function getGitCloneUrlPassword() {
        return $this->gitCloneUrlPassword;
    }
    
    public function getGitClonePath() {
        return $this->gitClonePath;
    }
    
    public function getUserGitScript() {
        return $this->userGitScript;
    }
    
    public function getUserWebScript() {
        return $this->userWebScript;
    }
    
    public function getRootWebPath() {
        return $this->rootWebPath;
    }
    
    public function getCommand() {
        return $this->command;
    }
    
    public function getActive() {
        return $this->active;
    }
}