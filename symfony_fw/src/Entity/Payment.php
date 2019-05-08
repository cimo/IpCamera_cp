<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="payment", options={"collate"="utf8_unicode_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 */
class Payment {
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(name="user_id", type="integer", columnDefinition="int(11) NOT NULL DEFAULT 0")
     */
    private $userId = 0;
    
    /**
     * @ORM\Column(name="transaction", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $transaction = "";
    
    /**
     * @ORM\Column(name="date", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $date = "";
    
    /**
     * @ORM\Column(name="status", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $status = "";
    
    /**
     * @ORM\Column(name="payer", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $payer = "";
    
    /**
     * @ORM\Column(name="receiver", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $receiver = "";
    
    /**
     * @ORM\Column(name="currency_code", type="string", columnDefinition="varchar(3) NOT NULL DEFAULT ''")
     */
    private $currencyCode = "";
    
    /**
     * @ORM\Column(name="item_name", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $itemName = "";
    
    /**
     * @ORM\Column(name="amount", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $amount = "";
    
    /**
     * @ORM\Column(name="quantity", type="string", columnDefinition="varchar(255) NOT NULL DEFAULT ''")
     */
    private $quantity = "";
    
    /**
     * @ORM\Column(name="status_delete", type="boolean", columnDefinition="tinyint(1) NOT NULL DEFAULT 0")
     */
    private $statusDelete = false;
    
    // Properties
    public function setUserId($value) {
        $this->userId = $value;
    }
    
    public function setTransaction($value) {
        $this->transaction = $value;
    }
    
    public function setDate($value) {
        $this->date = $value;
    }
    
    public function setStatus($value) {
        $this->status = $value;
    }
    
    public function setPayer($value) {
        $this->payer = $value;
    }
    
    public function setReceiver($value) {
        $this->receiver = $value;
    }
    
    public function setCurrencyCode($value) {
        $this->currencyCode = $value;
    }
    
    public function setItemName($value) {
        $this->itemName = $value;
    }
    
    public function setAmount($value) {
        $this->amount = $value;
    }
    
    public function setQuantity($value) {
        $this->quantity = $value;
    }
    
    public function setStatusDelete($value) {
        $this->statusDelete = $value;
    }
    
    // ---
    
    public function getId() {
        return $this->id;
    }
    
    public function getUserId() {
        return $this->userId;
    }
    
    public function getTransaction() {
        return $this->transaction;
    }
    
    public function getDate() {
        return $this->date;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function getPayer() {
        return $this->payer;
    }
    
    public function getReceiver() {
        return $this->receiver;
    }
    
    public function getCurrencyCode() {
        return $this->currencyCode;
    }
    
    public function getItemName() {
        return $this->itemName;
    }
    
    public function getAmount() {
        return $this->amount;
    }
    
    public function getQuantity() {
        return $this->quantity;
    }
    
    public function getStatusDelete() {
        return $this->statusDelete;
    }
}