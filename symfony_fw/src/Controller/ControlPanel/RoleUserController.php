<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
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
    
    private $helper;
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
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $roleUserEntity = new RoleUser();
        
        $this->session->set("roleUserProfileId", 0);
        
        $form = $this->createForm(RoleUserFormType::class, $roleUserEntity, Array(
            'validation_groups' => Array("roleUser_create")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($roleUserEntity);
                $this->entityManager->flush();

                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("roleUserController_1");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("roleUserController_2");
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
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        $this->tableAndPagination = new TableAndPagination($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $this->session->set("roleUserProfileId", 0);
        
        $roleUserRows = $this->query->selectAllRoleUserDatabase();
        
        $tableAndPagination = $this->tableAndPagination->request($roleUserRows, 20, "role", false);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(RoleUserSelectFormType::class, null, Array(
            'validation_groups' => Array("roleUser_select"),
            'id' => array_column($roleUserRows, "id", "level")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            $id = 0;
            
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true)
                $id = $request->get("id");
            else if ($form->isSubmitted() == true && $form->isValid() == true)
                $id = $form->get("id")->getData();
            
            if ($request->get("event") != "refresh" && $request->get("event") != "tableAndPagination") {
                $roleUserEntity = $this->entityManager->getRepository("App\Entity\RoleUser")->find($id);

                if ($roleUserEntity != null) {
                    $this->session->set("roleUserProfileId", $roleUserEntity->getId());

                    $formSub = $this->createForm(RoleUserFormType::class, $roleUserEntity, Array(
                        'validation_groups' => Array("roleUser_profile")
                    ));
                    $formSub->handleRequest($request);

                    $this->response['values']['id'] = $this->session->get("roleUserProfileId");

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/roleUser_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $formSub->createView()
                    ));
                }
                else {
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("roleUserController_3");
                    $this->response['errors'] = $this->ajax->errors($form);
                }
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
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $roleUserEntity = $this->entityManager->getRepository("App\Entity\RoleUser")->find($this->session->get("roleUserProfileId"));
        
        $form = $this->createForm(RoleUserFormType::class, $roleUserEntity, Array(
            'validation_groups' => Array("roleUser_profile")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($roleUserEntity);
                $this->entityManager->flush();
                
                $userRows = $this->query->selectAllUserDatabase();
                
                foreach ($userRows as $key => $value) {
                    $roleUserRow = $this->query->selectRoleUserDatabase($value['role_user_id']);
                    
                    $roleImplode = implode(",", $roleUserRow);
                    
                    $this->query->updateUserDatabase("role", $roleImplode, $value['id']);
                }
                
                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("roleUserController_4");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("roleUserController_5");
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
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $this->session->get("roleUserProfileId") : $request->get("id");

                    $roleUserDatabase = $this->query->deleteRoleUserDatabase("one", $id);

                    if ($roleUserDatabase == true) {
                        $this->query->deleteFromTableRoleUserDatabase("one", $this->urlLocale, $id);
                        
                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("roleUserController_6");
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $roleUserDatabase = $this->query->deleteRoleUserDatabase("all");

                    if ($roleUserDatabase == true) {
                        $this->query->deleteFromTableRoleUserDatabase("all", $this->urlLocale);
                        
                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("roleUserController_7");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("roleUserController_8");

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
}