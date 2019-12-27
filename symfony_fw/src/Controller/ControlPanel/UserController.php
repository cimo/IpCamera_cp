<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Entity\User;
use App\Form\UserFormType;
use App\Form\UserSelectFormType;

class UserController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $helper;
    private $query;
    private $ajax;
    private $tableAndPagination;
    
    private $session;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_user_create",
    *   path = "/cp_user_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/user_create.html.twig")
    */
    public function createAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator, UserPasswordEncoderInterface $passwordEncoder) {
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $userEntity = new User();
        
        $this->session->set("userProfileId", 0);
        
        $form = $this->createForm(UserFormType::class, $userEntity, Array(
            'validation_groups' => Array('user_create')
        ));
        $form->handleRequest($request);
        
        $this->response['values']['userRoleSelectHtml'] = $this->helper->createUserRoleSelectHtml("form_user_roleUserId_select", "userController_1", true);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $messagePassword = $this->helper->assignUserPassword("withoutOld", $userEntity, $form);

                if ($messagePassword == "ok") {
                    $this->helper->assignUserParameter($userEntity);

                    mkdir("{$this->helper->getPathPublic()}/files/user/{$form->get("username")->getData()}");
                    
                    $this->entityManager->persist($userEntity);
                    $this->entityManager->flush();

                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("userController_2");
                }
                else
                    $this->response['messages']['error'] = $messagePassword;
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("userController_3");
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
    *   name = "cp_user_select",
    *   path = "/cp_user_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/user_select.html.twig")
    */
    public function selectAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        $this->tableAndPagination = new TableAndPagination($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $this->session->set("userProfileId", 0);
        
        $userRows = $this->query->selectAllUserDatabase(1);
        
        $tableAndPagination = $this->tableAndPagination->request($userRows, 20, "user", false);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($userRows, $tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(UserSelectFormType::class, null, Array(
            'validation_groups' => Array('user_select'),
            'choicesId' => array_column($userRows, "id", "username")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
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
    
    /**
    * @Route(
    *   name = "cp_user_profile",
    *   path = "/cp_user_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/user_profile.html.twig")
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $id = $request->get("id");
                
                $userEntity = $this->entityManager->getRepository("App\Entity\User")->find($id);

                if ($userEntity != null) {
                    $this->session->set("userProfileId", $id);

                    $form = $this->createForm(UserFormType::class, $userEntity, Array(
                        'validation_groups' => Array('user_profile')
                    ));
                    $form->handleRequest($request);

                    $this->response['values']['userRoleSelectHtml'] = $this->helper->createUserRoleSelectHtml("form_user_roleUserId_select", "userController_1", true);
                    $this->response['values']['id'] = $this->session->get("userProfileId");
                    $this->response['values']['attemptLogin'] = $userEntity->getAttemptLogin();
                    $this->response['values']['credit'] = $userEntity->getCredit();

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/user_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $form->createView()
                    ));
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("userController_4");
            }
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
    *   name = "cp_user_profile_save",
    *   path = "/cp_user_profile_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/user_profile.html.twig")
    */
    public function profileSaveAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator, UserPasswordEncoderInterface $passwordEncoder) {
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $userEntity = $this->entityManager->getRepository("App\Entity\User")->find($this->session->get("userProfileId"));
        
        $form = $this->createForm(UserFormType::class, $userEntity, Array(
            'validation_groups' => Array('user_profile')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $messagePassword = $this->helper->assignUserPassword("withoutOld", $userEntity, $form);

                if ($messagePassword == "ok") {
                    $usernameOld = $userEntity->getUsername();
                    
                    if (file_exists("{$this->helper->getPathPublic()}/files/user/$usernameOld") == true)
                        rename("{$this->helper->getPathPublic()}/files/user/$usernameOld", "{$this->helper->getPathPublic()}/files/user/{$form->get("username")->getData()}");
                    
                    if ($form->get("active")->getData() == true)
                        $userEntity->setHelpCode("");
                    
                    $this->updateRoles($userEntity, $form->get("roleUserId")->getData());
                    
                    $this->entityManager->persist($userEntity);
                    $this->entityManager->flush();

                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("userController_5");
                }
                else
                    $this->response['messages']['error'] = $messagePassword;
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("userController_6");
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
    *   name = "cp_user_attemptLoginReset",
    *   path = "/cp_user_attemptLoginReset/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/user_profile.html.twig")
    */
    public function attemptLoginResetAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "reset") {
                    $userEntity = $this->entityManager->getRepository("App\Entity\User")->find($this->session->get("userProfileId"));
                    
                    $userEntity->setAttemptLogin(0);
                    
                    $this->entityManager->persist($userEntity);
                    $this->entityManager->flush();

                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("userController_7");
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("userController_8");
            }
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
    *   name = "cp_user_delete",
    *   path = "/cp_user_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/user_delete.html.twig")
    */
    public function deleteAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $this->session->get("userProfileId") : $request->get("id");

                    $userEntity = $this->entityManager->getRepository("App\Entity\User")->find($id);

                    $this->helper->removeDirRecursive("{$this->helper->getPathPublic()}/files/user/{$userEntity->getUsername()}", true);

                    $userDatabase = $this->userDatabase("delete", $userEntity->getId());

                    if ($userDatabase == true) {
                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("userController_9");
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $userRows = $this->query->selectAllUserDatabase(1);

                    for ($a = 0; $a < count($userRows); $a ++) {
                        $this->helper->removeDirRecursive("{$this->helper->getPathPublic()}/files/user/{$userRows[$a]['username']}", true);
                    }

                    $userDatabase = $this->userDatabase("deleteAll", null);

                    if ($userDatabase == true)
                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("userController_10");
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("userController_11");

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
            'response' => $this->response
        );
    }
    
    // Functions private    
    private function createListHtml($userRows, $elements) {
        $listHtml = "";
        
        $roleUserRow = Array();
        
        foreach ($userRows as $key => $value)
            $roleUserRow[] = $this->query->selectRoleUserDatabase($value['role_user_id'], true);
        
        foreach ($elements as $key => $value) {
            $listHtml .= "<tr>
                <td class=\"id_column\">
                    {$value['id']}
                </td>
                <td class=\"checkbox_column\">
                    <div class=\"mdc-checkbox\">
                        <input class=\"mdc-checkbox__native-control\" type=\"checkbox\"/>
                        <div class=\"mdc-checkbox__background\">
                            <svg class=\"mdc-checkbox__checkmark\" viewBox=\"0 0 24 24\">
                                <path class=\"mdc-checkbox__checkmark-path\" fill=\"none\" stroke=\"white\" d=\"M1.73,12.91 8.1,19.28 22.79,4.59\"/>
                            </svg>
                            <div class=\"mdc-checkbox__mixedmark\"></div>
                        </div>
                    </div>
                </td>
                <td>
                    {$value['username']}
                </td>
                <td>
                    {$value['email']}
                </td>
                <td>
                    {$value['company_name']}
                </td>
                <td>";
                    if ($value['active'] == 0)
                        $listHtml .= $this->helper->getTranslator()->trans("userController_12");
                    else
                        $listHtml .= $this->helper->getTranslator()->trans("userController_13");
                $listHtml .= "</td>
                <td>
                    <button class=\"mdc-fab mdc-fab--mini cp_user_delete\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function userDatabase($type, $id) {
        if ($type == "delete") {
            $query = $this->helper->getConnection()->prepare("DELETE FROM user
                                                                WHERE id > :idExclude
                                                                AND id = :id");
            
            $query->bindValue(":idExclude", 1);
            $query->bindValue(":id", $id);

            $query->execute();
            
            // payment
            $query = $this->helper->getConnection()->prepare("DELETE FROM payment
                                                                WHERE user_id > :idExclude
                                                                AND user_id = :id");
            
            $query->bindValue(":idExclude", 1);
            $query->bindValue(":id", $id);

            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->helper->getConnection()->prepare("DELETE FROM user
                                                                WHERE id > :idExclude");

            $query->bindValue(":idExclude", 1);

            $query->execute();
            
            // payment
            $query = $this->helper->getConnection()->prepare("DELETE FROM payment
                                                                WHERE user_id > :idExclude");
            
            $query->bindValue(":idExclude", 1);

            return $query->execute();
        }
    }
    
    private function updateRoles($user, $roleUserId) {
        $roleIds = $this->query->selectRoleUserDatabase($roleUserId);
        
        $user->setRoles($roleIds);
    }
}