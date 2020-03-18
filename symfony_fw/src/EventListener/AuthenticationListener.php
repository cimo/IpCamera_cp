<?php
namespace App\EventListener;

use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatorInterface;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;
use App\Classes\System\Captcha;

class AuthenticationListener implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface, LogoutSuccessHandlerInterface {
    // Vars
    private $container;
    private $entityManager;
    private $router;
    private $requestStack;
    
    private $response;
    
    private $helper;
    private $query;
    private $ajax;
    private $captcha;
    
    private $session;
    
    private $settingRow;
    
    // Properties
    
    // Functions public
    public function __construct(ContainerInterface $container, EntityManagerInterface $entityManager, Router $router, RequestStack $requestStack, TranslatorInterface $translator) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->requestStack = $requestStack;
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        $this->captcha = new Captcha($this->helper);
        
        $this->session = $this->helper->getSession();
        
        $this->settingRow = $this->helper->getSettingRow();
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        $referer = $request->headers->get("referer");
        
        if ($request->isXmlHttpRequest() == true) {
            $user = $token->getUser();
            
            $checkCaptcha = $this->captcha->check($this->settingRow['captcha'], $request->get("captcha"));
            $checkAttemptLogin = $this->helper->checkAttemptLogin("success", $user->getUsername());
            $checkUserActive = $this->helper->checkUserActive($user->getUsername());
            
            $arrayExplodeFindValue = $this->helper->arrayExplodeFindValue($this->settingRow['role_user_id'], $user->getRoleUserId());
            
            if ($checkCaptcha[0] == true && $checkAttemptLogin[0] == true && $checkUserActive[0] == true || ($this->settingRow['website_active'] == false && $arrayExplodeFindValue == true)) {
                $userRow = $this->query->selectUserDatabase($user->getId());
                
                $this->session->set("currentUser", $userRow);
                
                if ($request->get("_remember_me") != null)
                    $this->helper->createCookie("{$this->session->getName()}_remember_me", 1, 0, true, false);
                
                $this->helper->createCookie("{$this->session->getName()}_login", 1, 0, true, false);
                
                $this->response['values']['url'] = $referer;
            }
            else {
                $this->helper->getTokenStorage()->setToken(null);
                
                if ($checkCaptcha[0] == false) {
                    $message = $checkCaptcha[1];
                    
                    $this->response['values']['captchaReload'] = true;
                }
                else if ($checkAttemptLogin[0] == false)
                    $message = $checkAttemptLogin[1];
                else if ($checkUserActive[0] == false)
                    $message = $checkUserActive[1];
                
                $this->response['messages']['error'] = $message;
            }
            
            $this->response['values']['url'] = $referer;
            
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
        else
            return new RedirectResponse($referer);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        $referer = $request->headers->get("referer");
        
        if ($request->isXmlHttpRequest() == true) {
            $username = $request->get("_username");
            $password = $request->get("_password");
            
            if ($username == "")
                $message = $this->helper->getTranslator()->trans("authenticationListener_1");
            else if ($password == "")
                $message = $this->helper->getTranslator()->trans("authenticationListener_2");
            else {
                $checkCaptcha = $this->captcha->check($this->settingRow['captcha'], $request->get("captcha"));
                $checkAttemptLogin = $this->helper->checkAttemptLogin("failure", $username);
                $checkUserActive = $this->helper->checkUserActive($username);
                
                if ($checkCaptcha[0] == false) {
                    $message = $checkCaptcha[1];
                    
                    $this->response['values']['captchaReload'] = true;
                }
                else if ($checkAttemptLogin[0] == false)
                    $message = $checkAttemptLogin[1];
                else if ($checkUserActive[0] == false)
                    $message = $checkUserActive[1];
            }
            
            $this->response['messages']['error'] = $message;
            
            return $this->ajax->response(Array(
                'response' => $this->response
            ));
        }
        else
            return new RedirectResponse($referer);
    }
    
    public function onLogoutSuccess(Request $request) {
        $referer = $request->headers->get("referer");
        
        $url = $this->router->generate(
            "root_render",
            Array(
                '_locale' => $this->session->get("languageTextCode"),
                'urlCurrentPageId' => 2,
                'urlExtra' => ""
            )
        );
        
        $this->session->remove("currentUser");
        
        $this->helper->removeCookie("{$this->session->getName()}_remember_me");
        $this->helper->removeCookie("{$this->session->getName()}_login");
        
        return new RedirectResponse($url);
    }
    
    // Functions private
}