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

use App\Classes\System\Utility;

class RequestListener {
    // Vars
    private $container;
    private $entityManager;
    private $router;
    private $requestStack;
    
    private $utility;
    private $query;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManager $entityManager, Router $router, RequestStack $requestStack, TranslatorInterface $translator) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->requestStack = $requestStack;
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
    }
    
    public function onKernelRequest(GetResponseEvent $event) {
        if (HttpKernelInterface::MASTER_REQUEST != $event->getRequestType())
            return;
        
        $request = $event->getRequest();
        
        $session = $request->getSession();
        
        $settingRow = $this->query->selectSettingDatabase();
        
        $this->utility->configureCookie($session->getName(), time() + (10 * 365 * 24 * 60 * 60), $settingRow['https'], true);
        
        $checkLanguage = $this->utility->checkLanguage($request, $this->router, $settingRow);
        $request = $checkLanguage[0];
        
        $checkSessionOverTime = $this->utility->checkSessionOverTime($request, $this->router);
        
        $urlCurrentPageId = 2;
        
        if ($request->get("urlCurrentPageId") != null && $request->get("urlCurrentPageId") > 0)
             $urlCurrentPageId = $request->get("urlCurrentPageId");
        
        $phpSession = Array(
            'name' => $session->getName(),
            'userInform' => $session->get("userInform"),
            'languageTextCode' => $session->get("languageTextCode"),
            'currentPageId' => $urlCurrentPageId
        );
        
        $this->container->get("twig")->addGlobal("php_session", $phpSession);
        $this->container->get("twig")->addGlobal("websiteName", $this->utility->getWebsiteName());
        $this->container->get("twig")->addGlobal("settingRow", $settingRow);
        $this->container->get("twig")->addGlobal("pageRow", $this->query->selectPageDatabase($request->getLocale(), $urlCurrentPageId));
        
        if ($checkSessionOverTime != false)
            $event->setResponse(new RedirectResponse($checkSessionOverTime));
        else if ($checkLanguage[1] != false)
            $event->setResponse(new RedirectResponse($checkLanguage[1]));
        
        if ($settingRow['https'] == true) {
            if ($request->isSecure() == false) {
                $request->server->set("HTTPS", true);
                $request->server->set("SERVER_PORT", 443);
                
                $event->setResponse(new RedirectResponse($request->getUri()));
            }
        }
        else {
            if ($request->isSecure() == true) {
                $request->server->set("HTTPS", false);
                $request->server->set("SERVER_PORT", 80);
                
                $event->setResponse(new RedirectResponse($request->getUri()));
            }
        }
    }
    
    // Functions private
}