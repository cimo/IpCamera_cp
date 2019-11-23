<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Entity\RoleUser;
use App\Form\RoleUserFormType;
use App\Form\RoleUserSelectFormType;

class RoleUserController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    private $tableAndPagination;
    
    private $session;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_roleUser_create",
    *   path = "/cp_roleUser_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/roleUser_create.html.twig")
    */
    public function createAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->session = $this->utility->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $roleUserEntity = new RoleUser();
        
        $this->session->set("roleUserProfileId", 0);
        
        $form = $this->createForm(RoleUserFormType::class, $roleUserEntity, Array(
            'validation_groups' => Array('roleUser_create')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($roleUserEntity);
                $this->entityManager->flush();

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("roleUserController_1");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("roleUserController_2");
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
    *   name = "cp_roleUser_select",
    *   path = "/cp_roleUser_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/roleUser_select.html.twig")
    */
    public function selectAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        $this->tableAndPagination = new TableAndPagination($this->utility);
        
        $this->session = $this->utility->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $this->session->set("roleUserProfileId", 0);
        
        $userRoleRows = $this->query->selectAllRoleUserDatabase();
        
        $tableAndPagination = $this->tableAndPagination->request($userRoleRows, 20, "role", false);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(RoleUserSelectFormType::class, null, Array(
            'validation_groups' => Array('roleUser_select'),
            'choicesId' => array_column($userRoleRows, "id", "level")
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
    *   name = "cp_roleUser_profile",
    *   path = "/cp_roleUser_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/roleUser_profile.html.twig")
    */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->session = $this->utility->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $id = $request->get("id");
                
                $roleUserEntity = $this->entityManager->getRepository("App\Entity\RoleUser")->find($id);

                if ($roleUserEntity != null) {
                    $this->session->set("roleUserProfileId", $id);

                    $form = $this->createForm(RoleUserFormType::class, $roleUserEntity, Array(
                        'validation_groups' => Array('roleUser_profile')
                    ));
                    $form->handleRequest($request);

                    $this->response['values']['id'] = $this->session->get("roleUserProfileId");

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/roleUser_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $form->createView()
                    ));
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("roleUserController_3");
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
    *   name = "cp_roleUser_profile_save",
    *   path = "/cp_roleUser_profile_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/roleUser_profile.html.twig")
    */
    public function profileSaveAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->session = $this->utility->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $roleUserEntity = $this->entityManager->getRepository("App\Entity\RoleUser")->find($this->session->get("roleUserProfileId"));
        
        $form = $this->createForm(RoleUserFormType::class, $roleUserEntity, Array(
            'validation_groups' => Array('roleUser_profile')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($roleUserEntity);
                $this->entityManager->flush();
                
                $userRows = $this->query->selectAllUserDatabase();
                
                foreach ($userRows as $key => $value) {
                    $roleRow = $this->query->selectRoleUserDatabase($value['role_user_id']);
                    
                    $roleImplode = implode(",", $roleRow);
                    
                    $this->roleUserDatabase("update", $value['id'], $roleImplode);
                }
                
                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("roleUserController_4");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("roleUserController_5");
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
    *   name = "cp_roleUser_delete",
    *   path = "/cp_roleUser_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/roleUser_delete.html.twig")
    */
    public function deleteAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->session = $this->utility->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $this->session->get("roleUserProfileId") : $request->get("id");

                    $roleUserDatabase = $this->roleUserDatabase("delete", $id);

                    if ($roleUserDatabase == true) {
                        $this->deleteFromTable("delete", $id);
                        
                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("roleUserController_6");
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $roleUserDatabase = $this->roleUserDatabase("deleteAll");

                    if ($roleUserDatabase == true) {
                        $this->deleteFromTable("deleteAll");
                        
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("roleUserController_7");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("roleUserController_8");

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
    private function createListHtml($elements) {
        $listHtml = "";
        
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
                    {$value['level']}
                </td>";
                $listHtml .= "<td class=\"horizontal_center\">";
                    if ($value['id'] > 4)
                        $listHtml .= "<button class=\"mdc-fab mdc-fab--mini cp_roleUser_delete\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>";
                $listHtml .= "</td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function roleUserDatabase($type, $id = null, $roles = null) {
        if ($type == "update") {
            $query = $this->utility->getConnection()->prepare("UPDATE user
                                                                SET roles = :roles
                                                                WHERE id = :id");

            $query->bindValue(":roles", $roles);
            $query->bindValue(":id", $id);

            $query->execute();
        }
        else if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM role_user
                                                                WHERE id > :idExclude
                                                                AND id = :id");
            
            $query->bindValue(":idExclude", 4);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM role_user
                                                                WHERE id > :idExclude");
            
            $query->bindValue(":idExclude", 4);
            
            return $query->execute();
        }
    }
    
    private function deleteFromTable($type, $id = null) {
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale, null, true);
        $userRows = $this->query->selectAllUserDatabase(1);
        $settingRow = $this->query->selectSettingDatabase();
        
        if ($type == "delete") {
            foreach ($pageRows as $key => $value) {
                $roleExplode = explode(",", $value['role_user_id']);
                
                $key = array_search($id, $roleExplode);
                
                if ($key !== false) {
                    unset($roleExplode[$key]);
                    
                    $roleImplode = implode(",", $roleExplode);
                    
                    $query = $this->utility->getConnection()->prepare("UPDATE page
                                                                        SET role_user_id = :roleImplode
                                                                        WHERE id = :id");
                    
                    $query->bindValue(":roleImplode", $roleImplode);
                    $query->bindValue(":id", $value['id']);
                    
                    $query->execute();
                }
            }
            
            foreach ($userRows as $key => $value) {
                $roleExplode = explode(",", $value['role_user_id']);
                
                $key = array_search($id, $roleExplode);
                
                if ($key !== false) {
                    unset($roleExplode[$key]);
                    
                    $roleImplode = implode(",", $roleExplode);
                    
                    $roleUserRow = $this->query->selectRoleUserDatabase($roleImplode);
                    
                    $roleUserImplode = implode(",", $roleUserRow);
                    
                    $query = $this->utility->getConnection()->prepare("UPDATE user
                                                                        SET role_user_id = :roleImplode,
                                                                            roles = :roleUserImplode
                                                                        WHERE id = :id");
                    
                    $query->bindValue(":roleImplode", $roleImplode);
                    $query->bindValue(":roleUserImplode", $roleUserImplode);
                    $query->bindValue(":id", $value['id']);
                    
                    $query->execute();
                }
            }
            
            $roleExplode = explode(",", $settingRow['role_user_id']);
            
            $key = array_search($id, $roleExplode);
            
            if ($key !== false) {
                unset($roleExplode[$key]);
                
                $roleImplode = implode(",", $roleExplode);
                
                $query = $this->utility->getConnection()->prepare("UPDATE setting
                                                                        SET role_user_id = :roleImplode
                                                                        WHERE id = :id");
                
                $query->bindValue(":roleImplode", $roleImplode);
                $query->bindValue(":id", 1);
                
                $query->execute();
            }
        }
        else if ($type == "deleteAll") {
            foreach ($pageRows as $key => $value) {
                $roleImplode = $this->roleImplode($value['role_user_id']);
                
                $query = $this->utility->getConnection()->prepare("UPDATE page
                                                                    SET role_user_id = :roleImplode
                                                                    WHERE id = :id");
                
                $query->bindValue(":roleImplode", $roleImplode);
                $query->bindValue(":id", $value['id']);
                
                $query->execute();
            }
            
            foreach ($userRows as $key => $value) {
                $roleImplode = $this->roleImplode($value['role_user_id']);
                
                $roleUserRow = $this->query->selectRoleUserDatabase($roleImplode);
                
                $roleUserImplode = implode(",", $roleUserRow);
                
                $query = $this->utility->getConnection()->prepare("UPDATE user
                                                                    SET role_user_id = :roleImplode,
                                                                        roles = :roleUserImplode
                                                                    WHERE id = :id");
                
                $query->bindValue(":roleImplode", $roleImplode);
                $query->bindValue(":roleUserImplode", $roleUserImplode);
                $query->bindValue(":id", $value['id']);
                
                $query->execute();
            }
            
            $roleImplode = $this->roleImplode($settingRow['role_user_id']);
            
            $query = $this->utility->getConnection()->prepare("UPDATE setting
                                                                SET role_user_id = :roleImplode
                                                                WHERE id = :id");
            
            $query->bindValue(":roleImplode", $roleImplode);
            $query->bindValue(":id", 1);
            
            $query->execute();
        }
    }
    
    private function roleImplode($roleUserId) {
        $roleExplode = explode(",", $roleUserId);

        for ($a = 0; $a < count($roleExplode); $a ++) {
            if (intval($roleExplode[$a]) > 4)
                unset($roleExplode[$a]);
        }
        
        if (isset($roleExplode[0]) == false && empty($roleExplode[1]) == true)
            $roleExplode[1] = "1,";

        return implode(",", $roleExplode);
    }
}