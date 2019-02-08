<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
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
    
    private $utility;
    private $query;
    private $ajax;
    
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
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator, $passwordEncoder);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $settingRow = $this->query->selectSettingDatabase();
        
        if ($settingRow['recover_password'] == true) {
            $userRow = $this->query->selectUserWithHelpCodeDatabase($this->urlExtra);

            if ($userRow == false) {
                $this->response['values']['userId'] = $userRow['id'];

                $form = $this->createForm(RecoverPasswordFormType::class, null, Array(
                    'validation_groups' => Array('recover_password')
                ));
                $form->handleRequest($request);

                if ($request->isMethod("POST") == true) {
                    if ($form->isSubmitted() == true && $form->isValid() == true) {
                        $email = $form->get("email")->getData();

                        $userEntity = $this->entityManager->getRepository("App\Entity\User")->loadUserByUsername($email);

                        if ($userEntity != null) {
                            $helpCode = $this->utility->generateRandomString(20);

                            $userEntity->setHelpCode($helpCode);

                            $url = $this->utility->getUrlRoot() . $this->utility->getWebsiteFile() . "/" . $request->getLocale() . "/" . $request->get("urlCurrentPageId") . "/" . $helpCode;

                            // Send email to user
                            $this->utility->sendEmail($userEntity->getEmail(),
                                                        "Recover password",
                                                        "<p>Click on this link for reset your password:</p> <a href=\"$url\">$url</a>",
                                                        $settingRow['email_admin']);
                            
                            $this->entityManager->persist($userEntity);
                            $this->entityManager->flush();

                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("recoverPasswordController_1");
                        }
                        else
                            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("recoverPasswordController_2");
                    }
                    else {
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("recoverPasswordController_3");
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
                $userEntity = $this->entityManager->getRepository("App\Entity\User")->find($userRow['id']);

                $this->response['values']['userId'] = $userEntity->getId();

                $form = $this->createForm(ForgotPasswordFormType::class, null, Array(
                    'validation_groups' => Array('forgot_password')
                ));
                $form->handleRequest($request);

                if ($request->isMethod("POST") == true) {
                    if ($form->isSubmitted() == true && $form->isValid() == true) {
                        $messagePassword = $this->utility->assignUserPassword("withoutOld", $userEntity, $form);

                        if ($messagePassword == "ok") {
                            $userEntity->setHelpCode(null);
                            
                            $this->entityManager->persist($userEntity);
                            $this->entityManager->flush();

                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("recoverPasswordController_4");
                        }
                        else
                            $this->response['messages']['error'] = $messagePassword;
                    }
                    else {
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("recoverPasswordController_5");
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