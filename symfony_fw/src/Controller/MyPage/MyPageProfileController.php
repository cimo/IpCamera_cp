<?php
namespace App\Controller\MyPage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;

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
    
    private $helper;
    private $query;
    private $ajax;
    
    private $session;
    
    // Properties
    
    // Functions public
    /**
    * @Template("@templateRoot/render/myPage.html.twig")
    */
    public function renderAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
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
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_USER"), $this->getUser());
        
        $settingRow = $this->helper->getSettingRow();
        
        $usernameOld = $this->getUser()->getUsername();
        
        $avatar = "{$this->helper->getUrlRoot()}/images/templates/{$settingRow['template']}/no_avatar.jpg";
        
        if ($this->getUser()->getImage() != "" && file_exists("{$this->helper->getPathPublic()}/files/user/$usernameOld/{$this->getUser()->getImage()}") == true)
            $avatar = "{$this->helper->getUrlRoot()}/files/user/$usernameOld/{$this->getUser()->getImage()}";
        
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
                $this->fileUpload($form, $this->getUser());
                
                if ($form->has("username") == true) {
                    if (file_exists("{$this->helper->getPathPublic()}/files/user/$usernameOld") == true)
                        rename("{$this->helper->getPathPublic()}/files/user/$usernameOld", "{$this->helper->getPathPublic()}/files/user/{$form->get("username")->getData()}");
                }
                
                $this->entityManager->persist($this->getUser());
                $this->entityManager->flush();

                if ($form->has("username") == true && $form->get("username")->getData() != $usernameOld) {
                    $message = $this->helper->getTranslator()->trans("myPageProfileController_1");
                    
                    $this->session->set("userInform", $message);
                    
                    $this->response['messages']['info'] = $message;
                    
                    return $this->helper->forceLogout($this->router);
                }
                else
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("myPageProfileController_2");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("myPageProfileController_3");
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_USER"), $this->getUser());
        
        $form = $this->createForm(PasswordFormType::class, null, Array(
            'validation_groups' => Array('profile_password')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $messagePassword = $this->helper->assignUserPassword("withOld", $this->getUser(), $form);

                if ($messagePassword == "ok") {
                    $this->entityManager->persist($this->getUser());
                    $this->entityManager->flush();

                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("myPageProfileController_4");
                }
                else
                    $this->response['messages']['error'] = $messagePassword;
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("myPageProfileController_5");
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
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_USER"), $this->getUser());
        
        $settingRow = $this->helper->getSettingRow();
        
        if ($settingRow['credit'] == true) {
            $form = $this->createForm(CreditFormType::class, null, Array(
                'validation_groups' => Array('profile_credit')
            ));
            $form->handleRequest($request);

            $this->response['values']['currentCredit'] = $this->getUser() != null ? $this->getUser()->getCredit() : 0;
            $this->response['values']['payPalSandbox'] = $settingRow['payPal_sandbox'];

            if ($request->isMethod("POST") == true && $checkUserRole == true) {
                if ($form->isSubmitted() == true && $form->isValid() == true)
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("myPageProfileController_6");
                else {
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("myPageProfileController_7");
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
    *   name = "myPage_profile_credit_payPal",
    *   path = "/myPage_profile_credit_payPal/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    */
    public function creditPayPalAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        return new Response();
    }
    
    // Functions private
    private function fileUpload($form, $user) {
        $row = $this->query->selectUserDatabase($user->getId());
        
        $pathImage = "{$this->helper->getPathPublic()}/files/user/{$this->getUser()->getUsername()}";
        
        $image = $user->getImage();
        
        // Remove image
        if ($form->get("removeImage")->getData() == true) {
            if (file_exists("{$pathImage}/{$row['image']}") == true)
                unlink("{$pathImage}/{$row['image']}");
            
            $user->setImage("");
        }
        
        // Upload image
        if ($image != null && $form->get("removeImage")->getData() == false) {
            if ($row['image'] != "" && file_exists("{$pathImage}/{$row['image']}") == true)
                unlink("{$pathImage}/{$row['image']}");
            
            $fileName = $image->getClientOriginalName();
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newName = uniqid() . ".{$extension}";
            $image->move($pathImage, $newName);
            $user->setImage($newName);
        }
    }
}