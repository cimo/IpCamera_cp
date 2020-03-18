<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;

use App\Entity\User;
use App\Form\UserFormType;

class RegistrationController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $helper;
    private $query;
    private $ajax;
    
    private $session;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "registration",
    *   path = "/registration/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/registration.html.twig")
    */
    public function renderAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator, UserPasswordEncoderInterface $passwordEncoder) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator, $passwordEncoder);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $settingRow = $this->helper->getSettingRow();
        
        if ($settingRow['registration'] == true) {
            $helpCodeUserRow = $this->query->selectWithHelpCodeUserDatabase($this->urlExtra);
            
            if ($helpCodeUserRow == false) {
                $userEntity = new User();
                
                $form = $this->createForm(UserFormType::class, $userEntity, Array(
                    'validation_groups' => Array("registration")
                ));
                $form->handleRequest($request);
                
                if ($request->isMethod("POST") == true) {
                    if ($form->isSubmitted() == true && $form->isValid() == true) {
                        $messagePassword = $this->helper->assignUserPassword($userEntity, $form);
                        
                        if ($messagePassword === true) {
                            $this->helper->assignUserParameter($userEntity);
                            
                            $helpCode = $this->helper->generateRandomString(20);
                            
                            $userEntity->setDateRegistration($this->helper->dateFormat());
                            $userEntity->setHelpCode($helpCode);
                            
                            $url = "{$this->helper->getUrlRoot()}{$this->helper->getWebsiteFile()}/{$request->getLocale()}/{$request->get("urlCurrentPageId")}/{$helpCode}";
                            
                            $message = "";
                            
                            if ($settingRow['registration_user_confirm_admin'] == true)
                                $message = "<p>{$this->helper->getTranslator()->trans("registrationController_1")}</p><a href=\"{$url}\">${url}</a>";
                            else {
                                $message = "<p>{$this->helper->getTranslator()->trans("registrationController_2")}</p>";
                                
                                // Send email to admin
                                $this->helper->sendEmail(
                                    $settingRow['email_admin'],
                                    $this->helper->getTranslator()->trans("registrationController_3"),
                                    "<p>{$this->helper->getTranslator()->trans("registrationController_4")}<b>{$userEntity->getUsername()}</b>. {$this->helper->getTranslator()->trans("registrationController_5")}</p>",
                                    $settingRow['email_admin']
                                );
                            }
                            
                            // Send email to user
                            $this->helper->sendEmail(
                                $userEntity->getEmail(),
                                $this->helper->getTranslator()->trans("registrationController_3"),
                                $message,
                                $settingRow['email_admin']
                            );
                            
                            $userDirectory = "{$this->helper->getPathPublic()}/files/user/{$userEntity->getUsername()}";
                            
                            if (file_exists($userDirectory) == false)
                                mkdir($userDirectory);
                            
                            $this->entityManager->persist($userEntity);
                            $this->entityManager->flush();
                            
                            $this->response['messages']['success'] = $this->helper->getTranslator()->trans("registrationController_6");
                        }
                        else
                            $this->response['messages']['error'] = $messagePassword;
                    }
                    else {
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("registrationController_7");
                        $this->response['errors'] = $this->ajax->errors($form);
                    }
                    
                    return $this->ajax->response(Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                }
                
                return Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response,
                    'form' => $form->createView()
                );
            }
            else {
                $userEntity = $this->entityManager->getRepository("App\Entity\User")->find($helpCodeUserRow['id']);
                
                $userEntity->setActive(1);
                $userEntity->setHelpCode(null);
                $userEntity->setCredit(0);
                
                $this->entityManager->persist($userEntity);
                $this->entityManager->flush();
                
                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("registrationController_8");
                
                return Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response,
                    'form' => null
                );
            }
        }
        else
            return new Response();
    }
    
    // Functions private
}