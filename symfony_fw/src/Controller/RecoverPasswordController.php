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

use App\Form\RecoverPasswordFormType;
use App\Form\ForgotPasswordFormType;

class RecoverPasswordController extends AbstractController {
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
    *   name = "recover_password",
    *   path = "/recover_password/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/recover_password.html.twig")
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
        
        if ($settingRow['recover_password'] == true) {
            $helpCodeUserRow = $this->query->selectWithHelpCodeUserDatabase($this->urlExtra);

            if ($helpCodeUserRow == false) {
                $this->response['values']['userId'] = $helpCodeUserRow['id'];

                $form = $this->createForm(RecoverPasswordFormType::class, null, Array(
                    'validation_groups' => Array("recover_password")
                ));
                $form->handleRequest($request);

                if ($request->isMethod("POST") == true) {
                    if ($form->isSubmitted() == true && $form->isValid() == true) {
                        $email = $form->get("email")->getData();

                        $userEntity = $this->entityManager->getRepository("App\Entity\User")->loadUserByUsername($email);

                        if ($userEntity != null) {
                            $helpCode = $this->helper->generateRandomString(20);

                            $userEntity->setHelpCode($helpCode);

                            $url = $this->helper->getUrlRoot() . $this->helper->getWebsiteFile() . "/" . $request->getLocale() . "/" . $request->get("urlCurrentPageId") . "/" . $helpCode;

                            // Send email to user
                            $this->helper->sendEmail($userEntity->getEmail(),
                                                        $this->helper->getTranslator()->trans("recoverPasswordController_1"),
                                                        "<p>{$this->helper->getTranslator()->trans("recoverPasswordController_2")}</p> <a href=\"{$url}\">{$url}</a>",
                                                        $settingRow['email_admin']);
                            
                            $this->entityManager->persist($userEntity);
                            $this->entityManager->flush();

                            $this->response['messages']['success'] = $this->helper->getTranslator()->trans("recoverPasswordController_3");
                        }
                        else
                            $this->response['messages']['error'] = $this->helper->getTranslator()->trans("recoverPasswordController_4");
                    }
                    else {
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("recoverPasswordController_5");
                        $this->response['errors'] = $this->ajax->errors($form);
                    }

                    return $this->ajax->response(Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                }
            }
            else {
                $userEntity = $this->entityManager->getRepository("App\Entity\User")->find($helpCodeUserRow['id']);

                $this->response['values']['userId'] = $userEntity->getId();

                $form = $this->createForm(ForgotPasswordFormType::class, null, Array(
                    'validation_groups' => Array("forgot_password")
                ));
                $form->handleRequest($request);

                if ($request->isMethod("POST") == true) {
                    if ($form->isSubmitted() == true && $form->isValid() == true) {
                        $messagePassword = $this->helper->assignUserPassword($userEntity, $form);

                        if ($messagePassword === true) {
                            $userEntity->setHelpCode(null);
                            
                            $this->entityManager->persist($userEntity);
                            $this->entityManager->flush();

                            $this->response['messages']['success'] = $this->helper->getTranslator()->trans("recoverPasswordController_6");
                        }
                        else
                            $this->response['messages']['error'] = $messagePassword;
                    }
                    else {
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("recoverPasswordController_7");
                        $this->response['errors'] = $this->ajax->errors($form);
                    }

                    return $this->ajax->response(Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                }
            }

            return Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response,
                'form' => $form->createView()
            );
        }
        else
            return new Response();
    }
    
    // Functions private
}