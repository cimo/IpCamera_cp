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

use App\Entity\Page;
use App\Form\PageFormType;
use App\Form\PageSelectFormType;

class PageController extends AbstractController {
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
    
    private $listHtml;
    private $removedId;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_page_create",
    *   path = "/cp_page_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/page_create.html.twig")
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
        
        $pageEntity = new Page();
        
        $this->session->set("pageProfileId", 0);
        
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale);
        
        $form = $this->createForm(PageFormType::class, $pageEntity, Array(
            'validation_groups' => Array("page_create"),
            'urlLocale' => $this->urlLocale,
            'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId()),
            'parent' => array_flip($this->helper->createPageList($pageRows, true))
        ));
        $form->handleRequest($request);
        
        $parentPageRows = array_column($this->query->selectAllParentPageDatabase($form->get("parent")->getData()), "alias", "id");
        
        $this->response['values']['userRoleSelectHtml'] = $this->helper->createUserRoleSelectHtml("form_page_roleUserId_select", "pageController_1", true);
        $this->response['values']['pageSortListHtml'] = $this->helper->createPageSortListHtml($parentPageRows);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $pageEntity->setUserCreate($this->getUser()->getUsername());
                $pageEntity->setDateCreate($this->helper->dateFormat());
                
                if ($form->get("event")->getData() == "save_draft_create") {
                    $pageEntity->setAlias("{$form->get("alias")->getData()}_[draft]");
                    $pageEntity->setDraft(-1);
                }
                
                $this->entityManager->persist($pageEntity);
                $this->entityManager->flush();
                
                $pageDatabase = $this->query->insertPageDatabase($this->urlLocale, $form);
                
                if ($pageDatabase == true) {
                    $this->query->updateRankInMenuPageDatabase($pageEntity->getId(), $form->get("rankMenuSort")->getData());
                    
                    if ($form->get("event")->getData() == "save_draft_create")
                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("pageController_16");
                    else
                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("pageController_2");
                }
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("pageController_3");
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
    *   name = "cp_page_select",
    *   path = "/cp_page_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/page_select.html.twig")
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
        
        $this->listHtml = "";
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $this->session->set("pageProfileId", 0);
        
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale, null, true);
        
        $tableAndPagination = $this->tableAndPagination->request($pageRows, 20, "page", false, true);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(PageSelectFormType::class, null, Array(
            'validation_groups' => Array("page_select"),
            'id' => array_flip($this->helper->createPageList($pageRows, true))
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            $id = 0;
            
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true)
                $id = $request->get("id");
            else if ($form->isSubmitted() == true && $form->isValid() == true)
                $id = $form->get("id")->getData();
            
            if ($request->get("event") != "refresh" && $request->get("event") != "tableAndPagination") {
                $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($id);
                
                if ($pageEntity != null) {
                    $this->session->set("pageProfileId", $pageEntity->getId());
                    
                    $pageRows = $this->query->selectAllPageDatabase($this->urlLocale, null, true);
                    
                    $formSub = $this->createForm(PageFormType::class, $pageEntity, Array(
                        'validation_groups' => Array("page_profile"),
                        'urlLocale' => $this->urlLocale,
                        'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId(), true),
                        'parent' => array_flip($this->helper->createPageList($pageRows, true))
                    ));
                    $formSub->handleRequest($request);

                    $parentPageRows = array_column($this->query->selectAllParentPageDatabase($formSub->get("parent")->getData(), true), "alias", "id");
                    
                    $this->response['values']['pageId'] = $this->session->get("pageProfileId");
                    $this->response['values']['userRoleSelectHtml'] = $this->helper->createUserRoleSelectHtml("form_page_roleUserId_select", "pageController_1", true);
                    $this->response['values']['pageSortListHtml'] = $this->helper->createPageSortListHtml($parentPageRows, true);
                    $this->response['values']['userCreate'] = $pageEntity->getUserCreate();
                    $this->response['values']['dateCreate'] = $this->helper->dateFormat($pageEntity->getDateCreate());
                    $this->response['values']['userModify'] = $pageEntity->getUserModify();
                    $this->response['values']['dateModify'] = $this->helper->dateFormat($pageEntity->getDateModify());
                    $this->response['values']['draft'] = $pageEntity->getDraft();

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/page_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $formSub->createView()
                    ));
                }
                else {
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("pageController_4");
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
    *   name = "cp_page_profile",
    *   path = "/cp_page_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/page_profile.html.twig")
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
        
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale, null, true);
        
        $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($this->session->get("pageProfileId"));
        
        $form = $this->createForm(PageFormType::class, $pageEntity, Array(
            'validation_groups' => Array("page_profile"),
            'urlLocale' => $this->urlLocale,
            'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId(), true),
            'parent' => array_flip($this->helper->createPageList($pageRows, true))
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                if ($form->get("event")->getData() == "save_draft_modify") {
                    $pageDatabase = $this->query->draftPageDatabase("save", $this->urlLocale, $this->getUser(), $pageEntity->getId(), $form);
                    
                    if ($pageDatabase == true) {
                        $this->response['values']['id'] = $pageEntity->getId();
                        
                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("pageController_16");
                    }
                    else
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("pageController_17");
                }
                else if ($form->get("event")->getData() == "publish_draft") {
                    $pageDatabase = $this->query->draftPageDatabase("publish", $this->urlLocale, $this->getUser(), $pageEntity->getId(), null);
                    
                    if ($pageDatabase == true) {
                        $this->response['values']['id'] = $pageEntity->getId();
                        
                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("pageController_18");
                    }
                    else
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("pageController_19");
                }
                else {
                    $pageEntity->setUserModify($this->getUser()->getUsername());
                    $pageEntity->setDateModify($this->helper->dateFormat());
                    
                    $this->entityManager->persist($pageEntity);
                    $this->entityManager->flush();
                    
                    $pageDatabase = $this->query->updatePageDatabase($form, $pageEntity->getId());
                    
                    if ($pageDatabase == true) {
                        $this->query->updateRankInMenuPageDatabase($pageEntity->getId(), $form->get("rankMenuSort")->getData());
                        
                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("pageController_5");
                    }
                }
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("pageController_6");
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
    *   name = "cp_page_profile_sort",
    *   path = "/cp_page_profile_sort/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/page_profile.html.twig")
    */
    public function profileSortAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $id = $request->get("id") == null ? 0 : $request->get("id");
                
                $parentPageRows = array_column($this->query->selectAllParentPageDatabase($id, true), "alias", "id");
                
                $sessionPageProfileId = $this->session->get("pageProfileId");
                
                if ($sessionPageProfileId > 0) {
                    $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($sessionPageProfileId);
                    
                    $parentPageRows[$pageEntity->getId()] = $pageEntity->getAlias();
                }
                
                $this->response['values']['pageSortListHtml'] = $this->helper->createPageSortListHtml($parentPageRows, true);
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
    *   name = "cp_page_delete",
    *   path = "/cp_page_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/page_delete.html.twig")
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
                    $id = $request->get("id") == null ? $this->session->get("pageProfileId") : $request->get("id");

                    $childrenPageRows = $this->query->selectAllChildrenPageDatabase($id, true);

                    if ($childrenPageRows == false) {
                        $pageDatabase = $this->query->deletePageDatabase("one", $id);

                        if ($pageDatabase == true) {
                            $this->response['values']['id'] = $id;

                            $this->response['messages']['success'] = $this->helper->getTranslator()->trans("pageController_7");
                        }
                    }
                    else {
                        $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($id);
                        
                        $this->response['values']['pageId'] = $id;
                        $this->response['values']['parentId'] = $pageEntity->getParent();
                        $this->response['values']['text'] = "<p>" . $this->helper->getTranslator()->trans("pageController_8") . "</p>";
                        $this->response['values']['button'] = "<button id=\"cp_page_delete_parent_all\" class=\"mdc-button mdc-button--dense mdc-button--raised button_warning\" type=\"button\" style=\"display: block;\">" . $this->helper->getTranslator()->trans("pageController_9") . "</button>";
                        $this->response['values']['pageSelectHtml'] = $this->helper->createPageSelectHtml($this->urlLocale, "cp_page_delete_parent_new", $this->helper->getTranslator()->trans("pageController_10"), true);
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $pageDatabase = $this->query->deletePageDatabase("all");

                    if ($pageDatabase == true)
                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("pageController_11");
                }
                else if ($request->get("event") == "parentAll") {
                    $id = $request->get("id") == null ? $this->session->get("pageProfileId") : $request->get("id");

                    $this->removedId = Array();

                    $this->removePageChildren($id);

                    array_unshift($this->removedId, $id);

                    $pageDatabase = $this->query->deletePageDatabase("one", $id);

                    if ($pageDatabase == true) {
                        $this->response['values']['removedId'] = $this->removedId;

                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("pageController_11");
                    }
                }
                else if ($request->get("event") == "parentNew") {
                    $id = $request->get("id") == null ? $this->session->get("pageProfileId") : $request->get("id");

                    $this->query->updateChildrenPageDatabase($id, $request->get("parentNew"));

                    $pageDatabase = $this->query->deletePageDatabase("one", $id);

                    if ($pageDatabase == true) {
                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("pageController_12");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("pageController_13");

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
        foreach ($elements as $key => $value) {
            $this->listHtml .= "<tr>
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
                    </div>";
                    if ($value['draft'] != 0)
                        $this->listHtml .= "<i class=\"material-icons\">feedback</i>";
                $this->listHtml .= "</td>
                <td>
                    {$value['alias']}
                </td>
                <td>";
                    if ($value['protected'] == 0)
                        $this->listHtml .= $this->helper->getTranslator()->trans("pageController_14");
                    else
                        $this->listHtml .= $this->helper->getTranslator()->trans("pageController_15");
                $this->listHtml .= "</td>
                    <td>";
                        if ($value['show_in_menu'] == 0)
                            $this->listHtml .= $this->helper->getTranslator()->trans("pageController_14");
                        else
                            $this->listHtml .= $this->helper->getTranslator()->trans("pageController_15");
                $this->listHtml .= "</td>
                    <td>";
                        if ($value['only_link'] == 0)
                            $this->listHtml .= $this->helper->getTranslator()->trans("pageController_14");
                        else
                            $this->listHtml .= $this->helper->getTranslator()->trans("pageController_15");
                $this->listHtml .= "</td>
                <td>";
                    if ($value['id'] > 5)
                        $this->listHtml .= "<button class=\"mdc-fab mdc-fab--mini cp_page_delete icon_warning\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>";
                $this->listHtml .= "</td>
            </tr>";
            
            if (count($value['children']) > 0)
                $this->createListHtml($value['children']);
        }
        
        return $this->listHtml;
    }
    
    private function removePageChildren($id) {
        $childrenPageRows = $this->query->selectAllChildrenPageDatabase($id, true);
        
        for ($a = 0; $a < count($childrenPageRows); $a ++) {
            $this->removedId[] = $childrenPageRows[$a]['id'];
                    
            $this->removePageChildren($childrenPageRows[$a]['id']);
        }
        
        $this->query->deletePageDatabase("one", $id);
    }
}