<?php
namespace App\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatorInterface;

use App\Classes\System\Helper;

class RequestListener {
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
    public function __construct(ContainerInterface $container, EntityManager $entityManager, Router $router, RequestStack $requestStack, TranslatorInterface $translator) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->requestStack = $requestStack;
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        
        $this->session = $this->helper->getSession();
        
        $this->settingRow = $this->helper->getSettingRow();
    }
    
    public function onKernelRequest(GetResponseEvent $event) {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType())
            return;
        
        $this->helper->xssProtection();
        
        $request = $event->getRequest();
        
        $checkLanguage = $this->helper->checkLanguage($request, $this->router);
        $newRequest = $checkLanguage[0];
        
        //$checkSessionOverTime = $this->helper->checkSessionOverTime($newRequest, $this->router);
        
        $urlCurrentPageId = 2;
        
        if ($newRequest->get("urlCurrentPageId") != null && $newRequest->get("urlCurrentPageId") > 0)
             $urlCurrentPageId = $newRequest->get("urlCurrentPageId");
        
        $phpSession = Array(
            'name' => $this->session->getName(),
            'userInform' => $this->session->get("userInform"),
            'languageTextCode' => $this->session->get("languageTextCode"),
            'currentPageId' => $urlCurrentPageId,
            'xssProtectionValue' => $this->session->get("xssProtectionValue")
        );
        
        $this->container->get("twig")->addGlobal("php_session", $phpSession);
        $this->container->get("twig")->addGlobal("websiteName", $this->helper->getWebsiteName());
        $this->container->get("twig")->addGlobal("settingRow", $this->settingRow);
        $this->container->get("twig")->addGlobal("pageRow", $this->query->selectPageDatabase($newRequest->getLocale(), $urlCurrentPageId));
        
        if ($this->settingRow['javascript_minify'] == 1)
            $this->container->get("twig")->addGlobal("javascriptMinify", ".min.js");
        else
            $this->container->get("twig")->addGlobal("javascriptMinify", ".js");
        
        if ($checkLanguage[1] != false)
            $event->setResponse(new RedirectResponse($checkLanguage[1]));
        /*else if ($checkSessionOverTime != false)
            $event->setResponse(new RedirectResponse($checkSessionOverTime));*/
        
        if ($this->settingRow['https'] == true) {
            if ($newRequest->isSecure() == false) {
                $newRequest->server->set("HTTPS", true);
                $newRequest->server->set("SERVER_PORT", 443);
                
                $event->setResponse(new RedirectResponse($newRequest->getUri()));
            }
        }
        else {
            if ($newRequest->isSecure() == true) {
                $newRequest->server->set("HTTPS", false);
                $newRequest->server->set("SERVER_PORT", 80);
                
                $event->setResponse(new RedirectResponse($newRequest->getUri()));
            }
        }
    }
    
    // Functions private
}