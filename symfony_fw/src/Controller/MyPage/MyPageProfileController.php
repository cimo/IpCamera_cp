<?php
namespace App\Controller\MyPage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;
use App\Classes\System\Upload;

use App\Form\UserFormType;
use App\Form\PasswordFormType;
use App\Form\CreditFormType;

class MyPageProfileController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    private $upload;
    
    // Properties
    
    // Functions public
    /**
    * @Template("@templateRoot/render/myPage.html.twig")
    */
    public function renderAction($_locale, $urlCurrentPageId, $urlExtra, Request $request) {
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    /**
    * @Route(
    *   name = "myPage_profile",
    *   path = "/myPage_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/my_page/myPage_profile.html.twig")
    */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_USER"), $this->getUser());
        
        $usernameOld = $this->getUser()->getUsername();
        
        $settingRow = $this->query->selectSettingDatabase();
        
        $avatar = "{$this->utility->getUrlRoot()}/images/templates/{$settingRow['template']}/no_avatar.jpg";
        
        if (file_exists("{$this->utility->getPathPublic()}/files/user/$usernameOld/Avatar.jpg") == true)
            $avatar = "{$this->utility->getUrlRoot()}/files/user/$usernameOld/Avatar.jpg";
        
        $form = $this->createForm(UserFormType::class, $this->getUser(), Array(
            'validation_groups' => Array('profile')
        ));
        
        if ($this->getUser()->getId() > 1)
            $form->remove("username");
        
        $form->remove("roleUserId");
        $form->remove("password");
        $form->remove("active");
        
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                if ($form->has("username") == true) {
                    if (file_exists("{$this->utility->getPathPublic()}/files/user/$usernameOld") == true)
                        rename("{$this->utility->getPathPublic()}/files/user/$usernameOld", "{$this->utility->getPathPublic()}/files/user/{$form->get("username")->getData()}");
                }
                
                $this->entityManager->persist($this->getUser());
                $this->entityManager->flush();

                if ($form->has("username") == true && $form->get("username")->getData() != $usernameOld) {
                    $this->utility->getTokenStorage()->setToken(null);
                    
                    $message = $this->utility->getTranslator()->trans("myPageProfileController_1");
                    
                    $_SESSION['userInform'] = $message;
                    
                    $this->response['messages']['info'] = $message;
                }
                else
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("myPageProfileController_2");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("myPageProfileController_3");
                $this->response['errors'] = $this->ajax->errors($form);
            }
            
            return $this->ajax->response(Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response,
                'avatar' => $avatar
            ));
        }
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response,
            'form' => $form->createView(),
            'avatar' => $avatar
        );
    }
    
    /**
    * @Route(
    *   name = "myPage_profile_password",
    *   path = "/myPage_profile_password/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/my_page/myPage_profile_password.html.twig")
    */
    public function passwordAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator, UserPasswordEncoderInterface $passwordEncoder) {
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator, $passwordEncoder);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_USER"), $this->getUser());
        
        $form = $this->createForm(PasswordFormType::class, null, Array(
            'validation_groups' => Array('profile_password')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $messagePassword = $this->utility->assignUserPassword("withOld", $this->getUser(), $form);

                if ($messagePassword == "ok") {
                    $this->entityManager->persist($this->getUser());
                    $this->entityManager->flush();

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("myPageProfileController_4");
                }
                else
                    $this->response['messages']['error'] = $messagePassword;
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("myPageProfileController_5");
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
    
    /**
    * @Route(
    *   name = "myPage_profile_credit",
    *   path = "/myPage_profile_credit/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/my_page/myPage_profile_credit.html.twig")
    */
    public function creditAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_USER"), $this->getUser());
        
        $settingRow = $this->query->selectSettingDatabase();
        
        if ($settingRow['credit'] == true) {
            $form = $this->createForm(CreditFormType::class, null, Array(
                'validation_groups' => Array('profile_credit')
            ));
            $form->handleRequest($request);

            $this->response['values']['currentCredit'] = $this->getUser() != null ? $this->getUser()->getCredit() : 0;
            $this->response['values']['payPalSandbox'] = $settingRow['payPal_sandbox'];

            if ($request->isMethod("POST") == true && $checkUserRole == true) {
                if ($form->isSubmitted() == true && $form->isValid() == true)
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("myPageProfileController_6");
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("myPageProfileController_7");
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
        else
            return new Response();
    }
    
    /**
    * @Route(
    *   name = "myPage_profile_upload",
    *   path = "/myPage_profile_upload/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    */
    public function uploadAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        $this->upload = new Upload($this->utility);
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_USER"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            $path = "{$this->utility->getPathPublic()}/files/user/{$this->getUser()->getUsername()}";
            
            $this->upload->setSettings(Array(
                'path' => $path,
                'chunkSize' => 1048576,
                'inputType' => "single",
                'types' => Array("image/jpg", "image/jpeg"),
                'maxSize' => 2097152,
                'imageWidth' => 150,
                'imageHeight' => 150,
                'nameOverwrite' => "Avatar"
            ));
            $this->response['upload']['processFile'] = $this->upload->processFile();
        }
        
        return $this->ajax->response(Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        ));
    }
    
    /**
    * @Route(
    *   name = "myPage_profile_credit_payPal",
    *   path = "/myPage_profile_credit_payPal/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    */
    public function creditPayPalAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        return new Response();
    }
    
    // Functions private
}