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
    
    private $utility;
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
        
        $pageEntity = new Page();
        
        $this->session->set("pageProfileId", 0);
        
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale);
        
        $form = $this->createForm(PageFormType::class, $pageEntity, Array(
            'validation_groups' => Array('page_create'),
            'urlLocale' => $this->urlLocale,
            'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId()),
            'choicesParent' => array_flip($this->utility->createPageList($pageRows, true))
        ));
        $form->handleRequest($request);
        
        $pageParentRows = array_column($this->query->selectAllPageParentDatabase($form->get("parent")->getData()), "alias", "id");
        
        $this->response['values']['userRoleSelectHtml'] = $this->utility->createUserRoleSelectHtml("form_page_roleUserId_select", "pageController_1", true);
        $this->response['values']['pageSortListHtml'] = $this->utility->createPageSortListHtml($pageParentRows);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $pageEntity->setUserCreate($this->getUser()->getUsername());
                $pageEntity->setDateCreate(date("Y-m-d H:i:s"));
                
                if ($form->get("event")->getData() == "save_draft_create") {
                    $pageEntity->setAlias("{$form->get("alias")->getData()}_[draft]");
                    $pageEntity->setDraft(-1);
                }
                
                $this->entityManager->persist($pageEntity);
                $this->entityManager->flush();
                
                $pageDatabase = $this->pageDatabase("insert", $this->urlLocale, $pageEntity->getId(), $form);
                
                if ($pageDatabase == true) {
                    $this->updateRankInMenuDatabase($form->get("rankMenuSort")->getData(), $pageEntity->getId());
                    
                    if ($form->get("event")->getData() == "save_draft_create")
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_16");
                    else
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_2");
                }
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageController_3");
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
        
        $this->listHtml = "";
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $this->session->set("pageProfileId", 0);
        
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale, null, true);
        
        $tableAndPagination = $this->tableAndPagination->request($pageRows, 20, "page", false, true);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(PageSelectFormType::class, null, Array(
            'validation_groups' => Array('page_select'),
            'choicesId' => array_flip($this->utility->createPageList($pageRows, true))
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
                
                $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($id);
                
                if ($pageEntity != null) {
                    $this->session->set("pageProfileId", $pageEntity->getId());
                    
                    $pageRows = $this->query->selectAllPageDatabase($this->urlLocale, null, true);
                    
                    $form = $this->createForm(PageFormType::class, $pageEntity, Array(
                        'validation_groups' => Array('page_profile'),
                        'urlLocale' => $this->urlLocale,
                        'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId(), true),
                        'choicesParent' => array_flip($this->utility->createPageList($pageRows, true))
                    ));
                    $form->handleRequest($request);

                    $pageParentRows = array_column($this->query->selectAllPageParentDatabase($form->get("parent")->getData(), true), "alias", "id");
                    
                    $this->response['values']['pageId'] = $this->session->get("pageProfileId");
                    $this->response['values']['userRoleSelectHtml'] = $this->utility->createUserRoleSelectHtml("form_page_roleUserId_select", "pageController_1", true);
                    $this->response['values']['pageSortListHtml'] = $this->utility->createPageSortListHtml($pageParentRows, true);
                    $this->response['values']['userCreate'] = $pageEntity->getUserCreate();
                    $this->response['values']['dateCreate'] = $this->utility->dateFormat($pageEntity->getDateCreate());
                    $this->response['values']['userModify'] = $pageEntity->getUserModify();
                    $this->response['values']['dateModify'] = $this->utility->dateFormat($pageEntity->getDateModify());
                    $this->response['values']['draft'] = $pageEntity->getDraft();

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/page_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $form->createView()
                    ));
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageController_4");
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
                $rows = array_column($this->query->selectAllPageParentDatabase($request->get("id"), true), "alias", "id");
                
                $sessionPageProfileId = $this->session->get("pageProfileId");
                
                if ($sessionPageProfileId > 0) {
                    $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($sessionPageProfileId);
                    
                    $rows[$pageEntity->getId()] = $pageEntity->getAlias();
                }
                
                $this->response['values']['pageSortListHtml'] = $this->utility->createPageSortListHtml($rows, true);
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
    *   name = "cp_page_profile_save",
    *   path = "/cp_page_profile_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/page_profile.html.twig")
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
        
        $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($this->session->get("pageProfileId"));
        
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale, null, true);
        
        $form = $this->createForm(PageFormType::class, $pageEntity, Array(
            'validation_groups' => Array('page_profile'),
            'urlLocale' => $this->urlLocale,
            'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId(), true),
            'choicesParent' => array_flip($this->utility->createPageList($pageRows, true))
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                if ($form->get("event")->getData() == "save_draft_modify") {
                    $pageDatabase = $this->pageDatabase("save_draft_modify", $this->urlLocale, $pageEntity->getId(), $form);
                    
                    if ($pageDatabase == true) {
                        $this->response['values']['id'] = $pageEntity->getId();
                        
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_16");
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageController_17");
                }
                else if ($form->get("event")->getData() == "publish_draft") {
                    $pageDatabase = $this->pageDatabase("publish_draft", $this->urlLocale, $pageEntity->getId(), null);
                    
                    if ($pageDatabase == true) {
                        $this->response['values']['id'] = $pageEntity->getId();
                        
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_18");
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageController_19");
                }
                else {
                    $pageEntity->setUserModify($this->getUser()->getUsername());
                    $pageEntity->setDateModify(date("Y-m-d H:i:s"));

                    $this->entityManager->persist($pageEntity);
                    $this->entityManager->flush();

                    $pageDatabase = $this->pageDatabase("update", null, $pageEntity->getId(), $form);

                    if ($pageDatabase == true) {
                        $this->updateRankInMenuDatabase($form->get("rankMenuSort")->getData(), $pageEntity->getId());

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_5");
                    }
                }
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageController_6");
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
                    $id = $request->get("id") == null ? $this->session->get("pageProfileId") : $request->get("id");

                    $pageChildrenRows = $this->query->selectAllPageChildrenDatabase($id, true);

                    if ($pageChildrenRows == false) {
                        $pageDatabase = $this->pageDatabase("delete", null, $id, null);

                        if ($pageDatabase == true) {
                            $this->response['values']['id'] = $id;

                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_7");
                        }
                    }
                    else {
                        $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($id);
                        
                        $this->response['values']['pageId'] = $id;
                        $this->response['values']['parentId'] = $pageEntity->getParent();
                        $this->response['values']['text'] = "<p>" . $this->utility->getTranslator()->trans("pageController_8") . "</p>";
                        $this->response['values']['button'] = "<button id=\"cp_page_delete_parent_all\" class=\"mdc-button mdc-button--dense mdc-button--raised mdc-theme--secondary-bg\" type=\"button\" style=\"display: block;\">" . $this->utility->getTranslator()->trans("pageController_9") . "</button>";
                        $this->response['values']['pageSelectHtml'] = $this->utility->createPageSelectHtml($this->urlLocale, "cp_page_delete_parent_new", $this->utility->getTranslator()->trans("pageController_10"), true);
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $pageDatabase = $this->pageDatabase("deleteAll", null, null, null);

                    if ($pageDatabase == true)
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_11");
                }
                else if ($request->get("event") == "parentAll") {
                    $id = $request->get("id") == null ? $this->session->get("pageProfileId") : $request->get("id");

                    $this->removedId = Array();

                    $this->removePageChildrenDatabase($id);

                    array_unshift($this->removedId, $id);

                    $pageDatabase = $this->pageDatabase("delete", null, $id, null);

                    if ($pageDatabase == true) {
                        $this->response['values']['removedId'] = $this->removedId;

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_11");
                    }
                }
                else if ($request->get("event") == "parentNew") {
                    $id = $request->get("id") == null ? $this->session->get("pageProfileId") : $request->get("id");

                    $this->updatePageChildrenDatabase($id, $request->get("parentNew"));

                    $pageDatabase = $this->pageDatabase("delete", null, $id, null);

                    if ($pageDatabase == true) {
                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_12");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageController_13");

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
                        $this->listHtml .= $this->utility->getTranslator()->trans("pageController_14");
                    else
                        $this->listHtml .= $this->utility->getTranslator()->trans("pageController_15");
                $this->listHtml .= "</td>
                    <td>";
                        if ($value['show_in_menu'] == 0)
                            $this->listHtml .= $this->utility->getTranslator()->trans("pageController_14");
                        else
                            $this->listHtml .= $this->utility->getTranslator()->trans("pageController_15");
                $this->listHtml .= "</td>
                    <td>";
                        if ($value['only_link'] == 0)
                            $this->listHtml .= $this->utility->getTranslator()->trans("pageController_14");
                        else
                            $this->listHtml .= $this->utility->getTranslator()->trans("pageController_15");
                $this->listHtml .= "</td>
                <td>";
                    if ($value['id'] > 5)
                        $this->listHtml .= "<button class=\"mdc-fab mdc-fab--mini cp_page_delete\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>";
                $this->listHtml .= "</td>
            </tr>";
            
            if (count($value['children']) > 0)
                $this->createListHtml($value['children']);
        }
        
        return $this->listHtml;
    }
    
    private function removePageChildrenDatabase($id) {
        $pageChildrenRows = $this->query->selectAllPageChildrenDatabase($id, true);
        
        for ($a = 0; $a < count($pageChildrenRows); $a ++) {
            $this->removedId[] = $pageChildrenRows[$a]['id'];
                    
            $this->removePageChildrenDatabase($pageChildrenRows[$a]['id']);
        }
        
        $this->pageDatabase("delete", null, $id, null);
    }
    
    private function updatePageChildrenDatabase($id, $parentNew) {
        $query = $this->utility->getConnection()->prepare("UPDATE page
                                                            SET parent = :parentNew
                                                            WHERE parent = :id");
        
        $query->bindValue(":parentNew", $parentNew);
        $query->bindValue(":id", $id);
        
        $query->execute();
    }
    
    private function updateRankInMenuDatabase($rankMenuSort, $pageId) {
        $rankMenuSortExplode = explode(",", $rankMenuSort);
        array_pop($rankMenuSortExplode);
        
        foreach ($rankMenuSortExplode as $key => $value) {
            if (empty($value) == true)
                $value = $pageId;

            $query = $this->utility->getConnection()->prepare("UPDATE page
                                                                SET rank_in_menu = :rankInMenu
                                                                WHERE id = :id");

            $query->bindValue(":rankInMenu", $key + 1);
            $query->bindValue(":id", $value);

            $query->execute();
        }
    }
    
    private function pageDatabase($type, $urlLocale, $id, $form) {
        if ($type == "insert") {
            $query = $this->utility->getConnection()->prepare("INSERT INTO page_title (
                                                                    page_title.$urlLocale
                                                                )
                                                                VALUES (
                                                                    :title
                                                                );
                                                                INSERT INTO page_argument (
                                                                    page_argument.$urlLocale
                                                                )
                                                                VALUES (
                                                                    :argument
                                                                );
                                                                INSERT INTO page_menu_name (
                                                                    page_menu_name.$urlLocale
                                                                )
                                                                VALUES (
                                                                    :menuName
                                                                );");
            
            $query->bindValue(":title", $form->get("title")->getData());
            
            $argumentHtmlEntities = htmlentities($form->get("argument")->getData(), ENT_QUOTES, "UTF-8");
            $query->bindValue(":argument", $argumentHtmlEntities);
            
            $query->bindValue(":menuName", $form->get("menuName")->getData());
            
            return $query->execute();
        }
        else if ($type == "update") {
            $language = $form->get("language")->getData();
            
            $pageRow = $this->query->selectPageDatabase($language, $id, true);
            
            $alias = str_replace("_draft", "", $form->get("alias")->getData());
            $alias = $pageRow['draft'] > 0 ? "{$alias}_[draft]" : $alias;
            
            $query = $this->utility->getConnection()->prepare("UPDATE page, page_title, page_argument, page_menu_name
                                                                SET page.alias = :alias,
                                                                    page_title.$language = :title,
                                                                    page_argument.$language = :argument,
                                                                    page_menu_name.$language = :menuName
                                                                WHERE page.id = :id
                                                                AND page_title.id = :id
                                                                AND page_argument.id = :id
                                                                AND page_menu_name.id = :id");
            
            $query->bindValue(":alias", $alias);
            
            $query->bindValue(":title", $form->get("title")->getData());
            
            $argumentHtmlEntities = htmlentities($form->get("argument")->getData(), ENT_QUOTES, "UTF-8");
            $query->bindValue(":argument", $argumentHtmlEntities);
            
            $query->bindValue(":menuName", $form->get("menuName")->getData());
            
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM page WHERE id > :idExclude AND id = :id;
                                                                DELETE FROM page_title WHERE id > :idExclude AND id = :id;
                                                                DELETE FROM page_argument WHERE id > :idExclude AND id = :id;
                                                                DELETE FROM page_menu_name WHERE id > :idExclude AND id = :id;
                                                                DELETE FROM page_comment WHERE id > :idExclude AND id = :id;");
            
            $query->bindValue(":idExclude", 5);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM page WHERE id > :idExclude;
                                                                DELETE FROM page_title WHERE id > :idExclude;
                                                                DELETE FROM page_argument WHERE id > :idExclude;
                                                                DELETE FROM page_menu_name WHERE id > :idExclude;
                                                                DELETE FROM page_comment WHERE id > :idExclude;");
            
            $query->bindValue(":idExclude", 5);
            
            return $query->execute();
        }
        else if ($type == "save_draft_modify") {
            $alias = "{$form->get("alias")->getData()}_[draft]";
            
            $pageRows = $this->query->selectAllPageDatabase($urlLocale, null, true);
            
            foreach ($pageRows as $key => $value) {
                if (isset($value['alias']) == true) {
                    if ($alias == $value['alias'])
                        return false;
                }
            }
            
            $query = $this->utility->getConnection()->prepare("INSERT INTO page (
                                                                    alias,
                                                                    parent,
                                                                    controller_action,
                                                                    role_user_id,
                                                                    protected,
                                                                    show_in_menu,
                                                                    rank_in_menu,
                                                                    comment,
                                                                    only_parent,
                                                                    only_link,
                                                                    link,
                                                                    user_create,
                                                                    date_create,
                                                                    user_modify,
                                                                    date_modify,
                                                                    meta_description,
                                                                    meta_keywords,
                                                                    meta_robots,
                                                                    draft
                                                                )
                                                                SELECT 
                                                                    :alias,
                                                                    :parent,
                                                                    :controllerAction,
                                                                    :roleUserId,
                                                                    :protected,
                                                                    :showInMenu,
                                                                    rank_in_menu,
                                                                    :comment,
                                                                    :onlyParent,
                                                                    :onlyLink,
                                                                    :link,
                                                                    user_create,
                                                                    date_create,
                                                                    :userModify,
                                                                    :dateModify,
                                                                    :metaDescription,
                                                                    :metaKeywords,
                                                                    :metaRobots,
                                                                    :id
                                                                FROM 
                                                                    page
                                                                WHERE 
                                                                    id = :id");
            
            $query->bindValue(":alias", $alias);
            $query->bindValue(":parent", $form->get("parent")->getData());
            $query->bindValue(":controllerAction", $form->get("controllerAction")->getData());
            $query->bindValue(":roleUserId", $form->get("roleUserId")->getData());
            $query->bindValue(":protected", $form->get("protected")->getData());
            $query->bindValue(":showInMenu", $form->get("showInMenu")->getData());
            $query->bindValue(":comment", $form->get("comment")->getData());
            $query->bindValue(":onlyParent", $form->get("onlyParent")->getData());
            $query->bindValue(":onlyLink", $form->get("onlyLink")->getData());
            $query->bindValue(":link", $form->get("link")->getData());
            $query->bindValue(":userModify", $this->getUser()->getUsername());
            $query->bindValue(":dateModify", date("Y-m-d H:i:s"));
            $query->bindValue(":metaDescription", $form->get("metaDescription")->getData());
            $query->bindValue(":metaKeywords", $form->get("metaKeywords")->getData());
            $query->bindValue(":metaRobots", $form->get("metaRobots")->getData());
            $query->bindValue(":id", $id);

            $query->execute();
            
            $languageRows = $this->query->selectAllLanguageDatabase();
            
            $title = "";
            $argument = "";
            $menuName = "";
            $code = "";
            
            foreach($languageRows as $key => $value) {
                $title .= "page_title.{$value['code']},";
                $argument .= "page_argument.{$value['code']},";
                $menuName .= "page_menu_name.{$value['code']},";
                $code .= "{$value['code']},";
            }
            
            $title = substr($title, 0, -1);
            $argument = substr($argument, 0, -1);
            $menuName = substr($menuName, 0, -1);
            $code = substr($code, 0, -1);
            
            $query = $this->utility->getConnection()->prepare("INSERT INTO page_title (
                                                                    {$title}
                                                                )
                                                                SELECT
                                                                    {$code}
                                                                FROM
                                                                    page_title
                                                                WHERE
                                                                    id = :id;
                                                                INSERT INTO page_argument (
                                                                    {$argument}
                                                                )
                                                                SELECT
                                                                    {$code}
                                                                FROM
                                                                    page_argument
                                                                WHERE
                                                                    id = :id;
                                                                INSERT INTO page_menu_name (
                                                                    {$menuName}
                                                                )
                                                                SELECT
                                                                    {$code}
                                                                FROM
                                                                    page_menu_name
                                                                WHERE
                                                                    id = :id;");

            $query->bindValue(":id", $id);

            return $query->execute();
        }
        else if ($type == "publish_draft") {
            $pageRow = $this->query->selectPageDatabase($urlLocale, $id, true);
            
            $alias = str_replace("_[draft]", "", $pageRow['alias']);
            
            if ($pageRow['draft'] > 0) {
                $query = $this->utility->getConnection()->prepare("DELETE FROM page WHERE id > :idExclude AND id = :id;
                                                                    DELETE FROM page_title WHERE id > :idExclude AND id = :id;
                                                                    DELETE FROM page_argument WHERE id > :idExclude AND id = :id;
                                                                    DELETE FROM page_menu_name WHERE id > :idExclude AND id = :id;");

                $query->bindValue(":idExclude", 5);
                $query->bindValue(":id", $pageRow['draft']);

                $query->execute();
                
                $query = $this->utility->getConnection()->prepare("UPDATE page, page_title, page_argument, page_menu_name
                                                                    SET page.id = :newId,
                                                                        page.alias = :alias,
                                                                        page.draft = :draft,
                                                                        page_title.id = :newId,
                                                                        page_argument.id = :newId,
                                                                        page_menu_name.id = :newId
                                                                    WHERE page.id = :id 
                                                                    AND page_title.id = :id
                                                                    AND page_argument.id = :id
                                                                    AND page_menu_name.id = :id");

                $query->bindValue(":newId", $pageRow['draft']);
                $query->bindValue(":alias", $alias);
                $query->bindValue(":draft", 0);
                $query->bindValue(":id", $id);
            }
            else {
                $query = $this->utility->getConnection()->prepare("UPDATE page
                                                                    SET alias = :alias,
                                                                        draft = :draft
                                                                    WHERE id = :id");
                
                $query->bindValue(":alias", $alias);
                $query->bindValue(":draft", 0);
                $query->bindValue(":id", $id);
            }
            
            return $query->execute();
        }
    }
}