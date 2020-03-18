<?php
namespace App\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Translation\TranslatorInterface;

use App\Classes\System\Helper;
use App\Classes\System\PayPal;

use App\Entity\Payment;

class PayPalIpnListener {
    // Vars
    private $container;
    private $entityManager;
    private $router;
    private $requestStack;
    
    private $helper;
    private $query;
    
    private $session;
    
    private $settingRow;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManagerInterface $entityManager, Router $router, RequestStack $requestStack, TranslatorInterface $translator) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->requestStack = $requestStack;
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        
        $this->session = $this->helper->getSession();
        
        $this->settingRow = $this->helper->getSettingRow();
    }
    
    public function onKernelResponse(FilterResponseEvent $event) {
        $request = $event->getRequest();
        
        if (strpos($request->getUri(), "myPage_profile_credit_payPal") !== false) {
            $payPal = new PayPal(true, false, $this->settingRow['payPal_sandbox']);
            
            if ($payPal->ipn() == true) {
                $payPalElements = $payPal->getElements();

                $paymentRow = $this->query->selectPaymentDatabase($payPalElements['txn_id']);

                if ($paymentRow == false && isset($payPalElements['payment_status']) == true) {
                    if ($payPalElements['payment_status'] == "Completed") {
                        $payment = new Payment();
                        $payment->setUserId($payPalElements['custom']);
                        $payment->setTransaction($payPalElements['txn_id']);
                        $payment->setDate($payPalElements['payment_date']);
                        $payment->setStatus($payPalElements['payment_status']);
                        $payment->setPayer($payPalElements['payer_id']);
                        $payment->setReceiver($payPalElements['receiver_id']);
                        $payment->setCurrencyCode($payPalElements['mc_currency']);
                        $payment->setItemName($payPalElements['item_name']);
                        $payment->setAmount($payPalElements['mc_gross']);
                        $payment->setQuantity($payPalElements['quantity']);

                        $this->updateCredit($payPalElements);
                        
                        $this->entityManager->persist($payment);
                        $this->entityManager->flush();

                        error_log("Completed: " . print_r($payPalElements, true));
                    }
                    else if ($payPalElements['payment_status'] == "Pending")
                        error_log("Pending: " . print_r($payPalElements, true));
                }
            }
        }
    }
    
    // Functions private
    private function updateCredit($payPalElements) {
        $userRow = $this->query->selectUserDatabase($payPalElements['custom']);
        
        $credit = $userRow['credit'] + $payPalElements['quantity'];
        $id = $userRow['id'];
        
        $this->query->updateUserDatabase("credit", $credit, $id);
    }
}