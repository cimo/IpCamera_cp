<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * @ORM\Table(name="user", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"username"}, groups={"registration", "user_create", "user_profile"})
 * @UniqueEntity(fields={"email"}, groups={"registration", "user_create", "user_profile"})
 */
class User implements UserInterface, EquatableInterface, \Serializable {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="role_user_id", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT '1,'")
     */
    private $roleUserId = "1,";
    
    /**
     * @ORM\Column(name="image", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $image = "";
    
    private $removeImage = false;
    
    /**
     * @ORM\Column(name="username", type="string", columnDefinition="varchar(20) NOT NULL DEFAULT ''")
     */
    private $username = "";
    
    /**
     * @ORM\Column(name="name", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $name = "";
    
    /**
     * @ORM\Column(name="surname", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $surname = "";
    
    /**
     * @ORM\Column(name="email", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $email = "";
    
    /**
     * @ORM\Column(name="telephone", type="string", nullable=true, columnDefinition="varchar(20)")
     */
    private $telephone = "";
    
    /**
     * @ORM\Column(name="born", type="string", nullable=true, columnDefinition="varchar(10)")
     */
    private $born = "";
    
    /**
     * @ORM\Column(name="gender", type="string", nullable=true, columnDefinition="varchar(1)")
     */
    private $gender = "";
    
    /**
     * @ORM\Column(name="fiscal_code", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $fiscalCode = "";
    
    /**
     * @ORM\Column(name="company_name", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $companyName = "";
    
    /**
     * @ORM\Column(name="vat", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $vat = "";
    
    /**
     * @ORM\Column(name="website", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $website = "";
    
    /**
     * @ORM\Column(name="state", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $state = "";
    
    /**
     * @ORM\Column(name="city", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $city = "";
    
    /**
     * @ORM\Column(name="zip", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $zip = "";
    
    /**
     * @ORM\Column(name="address", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $address = "";
    
    /**
     * @ORM\Column(name="password", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $password = "";
    
    /**
     * @ORM\Column(name="credit", type="integer", columnDefinition="int(11) NOT NULL DEFAULT 0")
     */
    private $credit = 0;
    
    /**
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $active = false;
    
    /**
     * @ORM\Column(name="date_registration", type="string", columnDefinition="varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateRegistration = "0000-00-00 00:00:00";
    
    /**
     * @ORM\Column(name="date_current_login", type="string", columnDefinition="varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateCurrentLogin = "0000-00-00 00:00:00";
    
    /**
     * @ORM\Column(name="date_last_login", type="string", columnDefinition="varchar(19) NOT NULL DEFAULT '0000-00-00 00:00:00'")
     */
    private $dateLastLogin = "0000-00-00 00:00:00";
    
    /**
     * @ORM\Column(name="help_code", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $helpCode = "";
    
    /**
     * @ORM\Column(name="ip", type="string", nullable=true, columnDefinition="varchar(255)")
     */
    private $ip = "";
    
    /**
     * @ORM\Column(name="attempt_login", type="integer", columnDefinition="int(11) NOT NULL DEFAULT 0")
     */
    private $attemptLogin = 0;
    
    // Properties
    public function setRoleUserId($value) {
        $this->roleUserId = $value;
    }
    
    public function setImage($value) {
        $this->image = $value;
    }
    
    public function setRemoveImage($value) {
        $this->removeImage = $value;
    }
    
    public function setUsername($value) {
        $this->username = $value;
    }

    public function setName($value) {
        $this->name = $value;
    }

    public function setSurname($value) {
        $this->surname = $value;
    }

    public function setEmail($value) {
        $this->email = $value;
    }

    public function setTelephone($value) {
        $this->telephone = $value;
    }

    public function setBorn($value) {
        $this->born = $value;
    }

    public function setGender($value) {
        $this->gender = $value;
    }

    public function setFiscalCode($value) {
        $this->fiscalCode = $value;
    }

    public function setCompanyName($value) {
        $this->companyName = $value;
    }

    public function setVat($value) {
        $this->vat = $value;
    }

    public function setWebsite($value) {
        $this->website = $value;
    }
    
    public function setState($value) {
        $this->state = $value;
    }
    
    public function setCity($value) {
        $this->city = $value;
    }

    public function setZip($value) {
        $this->zip = $value;
    }
    
    public function setAddress($value) {
        $this->address = $value;
    }

    public function setPassword($value) {
        $this->password = $value;
    }
    
    public function setCredit($value) {
        $newValue = $value == null ? 0 : $value;
            
        $this->credit = $newValue;
    }
    
    public function setActive($value) {
        $this->active = $value;
    }
    
    public function setDateRegistration($value) {
        $this->dateRegistration = $value;
    }
    
    public function setDateCurrentLogin($value) {
        $this->dateCurrentLogin = $value;
    }
    
    public function setDateLastLogin($value) {
        $this->dateLastLogin = $value;
    }
    
    public function setHelpCode($value) {
        $this->helpCode = $value;
    }
    
    public function setIp($value) {
        $this->ip = $value;
    }
    
    public function setAttemptLogin($value) {
        $this->attemptLogin = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getRoleUserId() {
        return $this->roleUserId;
    }
    
    public function getImage() {
        return $this->image;
    }
    
    public function getRemoveImage() {
        return $this->removeImage;
    }

    public function getName() {
        return $this->name;
    }

    public function getSurname() {
        return $this->surname;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getTelephone() {
        return $this->telephone;
    }

    public function getBorn() {
        return $this->born;
    }

    public function getGender() {
        return $this->gender;
    }

    public function getFiscalCode() {
        return $this->fiscalCode;
    }

    public function getCompanyName() {
        return $this->companyName;
    }

    public function getVat() {
        return $this->vat;
    }

    public function getWebsite() {
        return $this->website;
    }
    
    public function getState() {
        return $this->state;
    }

    public function getCity() {
        return $this->city;
    }
    
    public function getZip() {
        return $this->zip;
    }
    
    public function getAddress() {
        return $this->address;
    }
    
    public function getCredit() {
        return $this->credit;
    }
    
    public function getActive() {
        return $this->active;
    }
    
    public function getDateRegistration() {
        return $this->dateRegistration;
    }
    
    public function getDateCurrentLogin() {
        return $this->dateCurrentLogin;
    }
    
    public function getDateLastLogin() {
        return $this->dateLastLogin;
    }
    
    public function getHelpCode() {
        return $this->helpCode;
    }
    
    public function getIp() {
        return $this->ip;
    }
    
    public function getAttemptLogin() {
        return $this->attemptLogin;
    }
    
    // Plus
    private $passwordConfirm = "";
    
    public function setPasswordConfirm($value) {
        $this->passwordConfirm = $value;
    }
    
    // ---
    
    public function getPasswordConfirm() {
        return $this->passwordConfirm;
    }
    
    // UserInterface
    private $salt = null;
    
    /**
     * @ORM\Column(name="roles", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT 'ROLE_USER,'")
     */
    private $roles = [];
    
    public function setRoles(array $roles) {
        $rolesImplode = implode(",", $roles);
        
        $this->roles = $rolesImplode;
    }
    
    // ---
    
    public function getUsername() {
        return $this->username;
    }
    
    public function getPassword() {
        return $this->password;
    }
    
    public function getSalt() {
        return $this->salt;
    }
    
    public function getRoles(): array {
        $rolesExplode = explode(",", $this->roles);
        
        if (in_array("ROLE_USER", $rolesExplode) === false)
            $rolesExplode[] = "ROLE_USER";
        
        return $rolesExplode;
    }
    
    public function eraseCredentials() {
    }
    
    // EquatableInterface
    public function isEqualTo(UserInterface $user) {
        if (!$user instanceof User)
            return false;
        
        if ($this->id !== $user->getId())
            return false;
        
        if ($this->username !== $user->getUsername())
            return false;
        
        if ($this->email !== $user->getEmail())
            return false;
        
        if ($this->password !== $user->getPassword())
            return false;

        return true;
    }
    
    public function serialize() {
        return serialize(array(
            $this->id,
            $this->username,
            $this->email,
            $this->password
        ));
    }
    
    public function unserialize($serialized) {
        list (
            $this->id,
            $this->username,
            $this->email,
            $this->password
        ) = unserialize($serialized, Array('allowed_classes' => false));
    }
}