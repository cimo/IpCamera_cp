<?php
namespace App\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Translation\TranslatorInterface;

use App\Classes\System\Helper;

class XssListener {
    // Vars
    private $container;
    private $entityManager;
    private $router;
    private $requestStack;
    
    private $helper;
    private $query;
    
    private $session;
    
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
    }
    
    public function onKernelResponse(FilterResponseEvent $event) {
        $response = $event->getResponse();
        
        $response->headers->set($this->session->get("xssProtectionTag"), $this->session->get("xssProtectionRule"));
    }
    
    // Functions private
}