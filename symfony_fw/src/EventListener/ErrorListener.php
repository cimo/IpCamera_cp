<?php
namespace App\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatorInterface;

use App\Classes\System\Helper;

class ErrorListener {
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
    
    public function onKernelException(GetResponseForExceptionEvent $event) {
        $exception = $event->getException();
        
        if ($exception instanceof NotFoundHttpException) {
            $request = $event->getRequest();
            
            if ($request->get("_route") == null) {
                $url = $this->router->generate(
                    "root_render",
                    Array(
                        '_locale' => $this->session->get("languageTextCode"),
                        'urlCurrentPageId' => 2,
                        'urlExtra' => "",
                        'error' => "404"
                    )
                );
                
                $event->setResponse(new RedirectResponse($url));
            }
        }
    }
    
    // Functions private
}