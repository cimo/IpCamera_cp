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
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $settingRow = $this->query->selectSettingDatabase();
        
        if ($settingRow['registration'] == true) {
            $userRow = $this->query->selectUserWithHelpCodeDatabase($this->urlExtra);

            if ($userRow == false) {
                $userEntity = new User();

                $form = $this->createForm(UserFormType::class, $userEntity, Array(
                    'validation_groups' => Array('registration')
                ));
                $form->handleRequest($request);

                if ($request->isMethod("POST") == true) {
                    if ($form->isSubmitted() == true && $form->isValid() == true) {
                        $messagePassword = $this->helper->assignUserPassword("withoutOld", $userEntity, $form);

                        if ($messagePassword == "ok") {
                            $this->helper->assignUserParameter($userEntity);

                            $helpCode = $this->helper->generateRandomString(20);

                            $userEntity->setDateRegistration(date("Y-m-d H:i:s"));
                            $userEntity->setHelpCode($helpCode);

                            $url = $this->helper->getUrlRoot() . $this->helper->getWebsiteFile() . "/" . $request->getLocale() . "/" . $request->get("urlCurrentPageId") . "/" . $helpCode;

                            $messageEmail = "";

                            if ($settingRow['registration_user_confirm_admin'] == true)
                                $messageEmail = "<p>" . $this->helper->getTranslator()->trans("registrationController_1") . "</p><a href=\"$url\">$url</a>";
                            else {
                                $messageEmail = "<p>" . $this->helper->getTranslator()->trans("registrationController_2") . "</p>";

                                // Send email to admin
                                $this->helper->sendEmail(
                                    $settingRow['email_admin'],
                                    $this->helper->getTranslator()->trans("registrationController_3"),
                                    "<p>" . $this->helper->getTranslator()->trans("registrationController_4") . "<b>" . $userEntity->getUsername() . "</b>. " . $this->helper->getTranslator()->trans("registrationController_5") . "</p>",
                                    $settingRow['email_admin']
                                );
                            }

                            // Send email to user
                            $this->helper->sendEmail(
                                $userEntity->getEmail(),
                                $this->helper->getTranslator()->trans("registrationController_3"),
                                $messageEmail,
                                $settingRow['email_admin']
                            );
                            
                            if (file_exists("{$this->helper->getPathPublic()}/files/user/{$userEntity->getUsername()}") == false)
                                mkdir("{$this->helper->getPathPublic()}/files/user/{$userEntity->getUsername()}");
                            
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
                $userEntity = $this->entityManager->getRepository("App\Entity\User")->find($userRow['id']);

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