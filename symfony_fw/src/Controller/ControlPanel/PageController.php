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
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $pageEntity = new Page();
        
        $_SESSION['pageProfileId'] = 0;
        
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
                
                $this->entityManager->persist($pageEntity);
                $this->entityManager->flush();

                $pageDatabase = $this->pageDatabase("insert", null, $this->urlLocale, $form);

                if ($pageDatabase == true) {
                    $this->updateRankInMenuDatabase($form->get("rankMenuSort")->getData(), $pageEntity->getId());
                    
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
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        $this->tableAndPagination = new TableAndPagination($this->utility);
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $_SESSION['pageProfileId'] = 0;
        
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale);
        
        $tableAndPagination = $this->tableAndPagination->request($pageRows, 20, "page", false, true);
        
        $this->listHtml = "";
        
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
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $id = $request->get("id");
                
                $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($id);

                if ($pageEntity != null) {
                    $_SESSION['pageProfileId'] = $id;

                    $pageRows = $this->query->selectAllPageDatabase($this->urlLocale);
                    
                    $form = $this->createForm(PageFormType::class, $pageEntity, Array(
                        'validation_groups' => Array('page_profile'),
                        'urlLocale' => $this->urlLocale,
                        'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId()),
                        'choicesParent' => array_flip($this->utility->createPageList($pageRows, true))
                    ));
                    $form->handleRequest($request);

                    $pageParentRows = array_column($this->query->selectAllPageParentDatabase($form->get("parent")->getData()), "alias", "id");
                    
                    $this->response['values']['userRoleSelectHtml'] = $this->utility->createUserRoleSelectHtml("form_page_roleUserId_select", "pageController_1", true);
                    $this->response['values']['pageSortListHtml'] = $this->utility->createPageSortListHtml($pageParentRows);
                    $this->response['values']['idPage'] = $_SESSION['pageProfileId'];
                    $this->response['values']['userCreate'] = $pageEntity->getUserCreate();
                    $this->response['values']['dateCreate'] = $this->utility->dateFormat($pageEntity->getDateCreate());
                    $this->response['values']['userModify'] = $pageEntity->getUserModify();
                    $this->response['values']['dateModify'] = $this->utility->dateFormat($pageEntity->getDateModify());

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
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $rows = array_column($this->query->selectAllPageParentDatabase($request->get("id")), "alias", "id");
                
                if ($_SESSION['pageProfileId'] > 0) {
                    $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($_SESSION['pageProfileId']);
                    
                    $rows[$pageEntity->getId()] = $pageEntity->getAlias();
                }
                
                $this->response['values']['pageSortListHtml'] = $this->utility->createPageSortListHtml($rows);
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
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR"), $this->getUser());
        
        $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($_SESSION['pageProfileId']);
        
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale);
        
        $form = $this->createForm(PageFormType::class, $pageEntity, Array(
            'validation_groups' => Array('page_profile'),
            'urlLocale' => $this->urlLocale,
            'pageRow' => $this->query->selectPageDatabase($this->urlLocale, $pageEntity->getId()),
            'choicesParent' => array_flip($this->utility->createPageList($pageRows, true))
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $pageEntity->setUserModify($this->getUser()->getUsername());
                $pageEntity->setDateModify(date("Y-m-d H:i:s"));
                
                $this->entityManager->persist($pageEntity);
                $this->entityManager->flush();

                $pageDatabase = $this->pageDatabase("update", $pageEntity->getId(), null, $form);

                if ($pageDatabase == true) {
                    $this->updateRankInMenuDatabase($form->get("rankMenuSort")->getData(), $pageEntity->getId());

                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_5");
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
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $_SESSION['pageProfileId'] : $request->get("id");

                    $pageChildrenRows = $this->query->selectAllPageChildrenDatabase($id);

                    if ($pageChildrenRows == false) {
                        $pageDatabase = $this->pageDatabase("delete", $id, null, null);

                        if ($pageDatabase == true) {
                            $this->response['values']['id'] = $id;

                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_7");
                        }
                    }
                    else {
                        $pageEntity = $this->entityManager->getRepository("App\Entity\Page")->find($id);
                        
                        $this->response['values']['idPage'] = $id;
                        $this->response['values']['idParent'] = $pageEntity->getParent();
                        $this->response['values']['text'] = "<p>" . $this->utility->getTranslator()->trans("pageController_8") . "</p>";
                        $this->response['values']['button'] = "<button id=\"cp_page_delete_parent_all\" class=\"mdc-button mdc-button--dense mdc-button--raised mdc-theme--secondary-bg\" type=\"button\" style=\"display: block;\">" . $this->utility->getTranslator()->trans("pageController_9") . "</button>";
                        $this->response['values']['pageSelectHtml'] = $this->utility->createPageSelectHtml($this->urlLocale, "cp_page_delete_parent_new", $this->utility->getTranslator()->trans("pageController_10"));
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $pageDatabase = $this->pageDatabase("deleteAll", null, null, null);

                    if ($pageDatabase == true)
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_11");
                }
                else if ($request->get("event") == "parentAll") {
                    $id = $request->get("id") == null ? $_SESSION['pageProfileId'] : $request->get("id");

                    $this->removedId = Array();

                    $this->removePageChildrenDatabase($id);

                    array_unshift($this->removedId, $id);

                    $pageDatabase = $this->pageDatabase("delete", $id, null, null);

                    if ($pageDatabase == true) {
                        $this->response['values']['removedId'] = $this->removedId;

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageController_11");
                    }
                }
                else if ($request->get("event") == "parentNew") {
                    $id = $request->get("id") == null ? $_SESSION['pageProfileId'] : $request->get("id");

                    $this->updatePageChildrenDatabase($id, $request->get("parentNew"));

                    $pageDatabase = $this->pageDatabase("delete", $id, null, null);

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
                    </div>
                </td>
                <td>
                    {$value['alias']}
                </td>
                <td>
                    {$value['title']}
                </td>
                <td>
                    {$value['menu_name']}
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
                        $this->listHtml .= "<button class=\"mdc-fab mdc-fab--mini cp_page_delete\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
            
            if (count($value['children']) > 0)
                $this->createListHtml($value['children']);
        }
        
        return $this->listHtml;
    }
    
    private function removePageChildrenDatabase($id) {
        $pageChildrenRows = $this->query->selectAllPageChildrenDatabase($id);
        
        for ($a = 0; $a < count($pageChildrenRows); $a ++) {
            $this->removedId[] = $pageChildrenRows[$a]['id'];
                    
            $this->removePageChildrenDatabase($pageChildrenRows[$a]['id']);
        }
        
        $this->pageDatabase("delete", $id, null, null);
    }
    
    private function updatePageChildrenDatabase($id, $parentNew) {
        $query = $this->utility->getConnection()->prepare("UPDATE pages
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

            $query = $this->utility->getConnection()->prepare("UPDATE pages
                                                                SET rank_in_menu = :rankInMenu
                                                                WHERE id = :id");

            $query->bindValue(":rankInMenu", $key + 1);
            $query->bindValue(":id", $value);

            $query->execute();
        }
    }
    
    private function pageDatabase($type, $id, $urlLocale, $form) {
        if ($type == "insert") {
            $query = $this->utility->getConnection()->prepare("INSERT INTO pages_titles (
                                                                    pages_titles.$urlLocale
                                                                )
                                                                VALUES (
                                                                    :title
                                                                );
                                                                INSERT INTO pages_arguments (
                                                                    pages_arguments.$urlLocale
                                                                )
                                                                VALUES (
                                                                    :argument
                                                                );
                                                                INSERT INTO pages_menu_names (
                                                                    pages_menu_names.$urlLocale
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
            
            $query = $this->utility->getConnection()->prepare("UPDATE pages_titles, pages_arguments, pages_menu_names
                                                                SET pages_titles.$language = :title,
                                                                    pages_arguments.$language = :argument,
                                                                    pages_menu_names.$language = :menuName
                                                                WHERE pages_titles.id = :id
                                                                AND pages_arguments.id = :id
                                                                AND pages_menu_names.id = :id");
            
            $query->bindValue(":title", $form->get("title")->getData());
            
            $argumentHtmlEntities = htmlentities($form->get("argument")->getData(), ENT_QUOTES, "UTF-8");
            $query->bindValue(":argument", $argumentHtmlEntities);
            
            $query->bindValue(":menuName", $form->get("menuName")->getData());
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE pages, pages_titles, pages_arguments, pages_menu_names, pages_comments FROM pages, pages_titles, pages_arguments, pages_menu_names, pages_comments
                                                                WHERE pages.id > :idExclude
                                                                AND pages.id = :id
                                                                AND pages_titles.id = :id
                                                                AND pages_arguments.id = :id
                                                                AND pages_menu_names.id = :id
                                                                AND pages_comments.page_id = :id");
            
            $query->bindValue(":idExclude", 5);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("DELETE pages, pages_titles, pages_arguments, pages_menu_names, pages_comments FROM pages, pages_titles, pages_arguments, pages_menu_names, pages_comments
                                                                WHERE pages.id > :idExclude
                                                                AND pages_titles.id > :idExclude
                                                                AND pages_arguments.id > :idExclude
                                                                AND pages_menu_names.id > :idExclude
                                                                AND pages_comments.page_id > :idExclude");
            
            $query->bindValue(":idExclude", 5);
            
            return $query->execute();
        }
    }
}