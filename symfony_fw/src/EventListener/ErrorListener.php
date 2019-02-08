<?php
namespace App\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatorInterface;

use App\Classes\System\Utility;

class ErrorListener {
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
    }
    
    public function onKernelException(GetResponseForExceptionEvent $event) {
        $exception = $event->getException();

        if ($exception instanceof NotFoundHttpException) {
            if ($event->getRequest()->get("_route") == null) {
                $session = $this->requestStack->getCurrentRequest()->getSession();
                $session = $session->get("php_session");
                
                $url = $this->router->generate(
                    "root_render",
                    Array(
                        '_locale' => $session['languageTextCode'],
                        'urlCurrentPageId' => 2,
                        'urlExtra' => "",
                        'error' => "404"
                    )
                );
                
                $response = new RedirectResponse($url);
                
                $event->setResponse($response);
            }
        }
    }
    
    // Functions private
}