<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Entity\PageComment;
use App\Form\PageCommentFormType;

class PageCommentController extends AbstractController {
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
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "pageComment_render",
    *   path = "/pageComment_render/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/include/pageComment.html.twig")
    */
    public function renderAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        $settingRow = $this->query->selectSettingDatabase();
        
        if ($settingRow['pageComment'] == true) {
            $pageCommentRows = $this->query->selectAllPageCommentDatabase($this->urlCurrentPageId);

            $tableAndPagination = $this->tableAndPagination->request($pageCommentRows, 20, "pageComment", false, true);

            $this->response['values']['search'] = $tableAndPagination['search'];
            $this->response['values']['pagination'] = $tableAndPagination['pagination'];
            $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
            $this->response['values']['count'] = $tableAndPagination['count'];

            if ($this->tableAndPagination->checkPost() == true) {
                return $this->ajax->response(Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response
                ));
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageCommentController_1");

            return Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response
            );
        }
        else
            return new Response();
    }
    
    /**
    * @Route(
    *   name = "pageComment_save",
    *   path = "/pageComment_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/include/pageComment.html.twig")
    */
    public function saveAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $settingRow = $this->query->selectSettingDatabase();
        $pageRow = $this->query->selectPageDatabase($this->urlLocale, $this->urlCurrentPageId);
        
        if ($settingRow['pageComment'] == true && $settingRow['pageComment_active'] == true && $pageRow['comment'] == true) {
            $pageCommentEntity = new PageComment();

            $form = $this->createForm(PageCommentFormType::class, $pageCommentEntity, Array(
                'validation_groups' => Array('page_comment')
            ));
            $form->handleRequest($request);

            if ($request->isMethod("POST") == true) {
                if ($form->isSubmitted() == true && $form->isValid() == true) {
                    $pageCommentRows = $this->query->selectAllPageCommentDatabase($this->urlCurrentPageId);
                    
                    $typeExplode = explode("_", $form->get("type")->getData());

                    if ($typeExplode[0] == "new") {
                        if ($pageCommentRows[count($pageCommentRows) - 1]['username'] !== $this->getUser()->getUsername()) {
                            $pageCommentEntity->setPageId($this->urlCurrentPageId);
                            $pageCommentEntity->setUsername($this->getUser()->getUsername());
                            $pageCommentEntity->setDateCreate(date("Y-m-d H:i:s"));
                            
                            $this->entityManager->persist($pageCommentEntity);
                            $this->entityManager->flush();

                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageCommentController_2");
                        }
                        else
                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageCommentController_3");
                    }
                    else if ($typeExplode[0] == "reply") {
                        $pageCommentRow = $this->query->selectPageCommentDatabase("single", $typeExplode[1]);

                        if ($pageCommentRow != false) {
                            $pageCommentEntity->setPageId($this->urlCurrentPageId);
                            $pageCommentEntity->setUsername($this->getUser()->getUsername());
                            $pageCommentEntity->setIdReply($typeExplode[1]);
                            $pageCommentEntity->setDateCreate(date("Y-m-d H:i:s"));
                            
                            $this->entityManager->persist($pageCommentEntity);
                            $this->entityManager->flush();

                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageCommentController_4");
                        }
                    }
                    else if ($typeExplode[0] == "edit") {
                        $this->pageCommentDatabase($typeExplode[1], $form->get("argument")->getData());

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("pageCommentController_5");
                    }
                    else {
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageCommentController_6");
                        $this->response['errors'] = $this->ajax->errors($form);
                    }
                }
                else {
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("pageCommentController_6");
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
    
    // Functions private
    private function createListHtml($elements) {
        $setting = $this->query->selectSettingDatabase();
        
        $html = "<ul class=\"mdc-list mdc-list--two-line mdc-list--avatar-list\">";
        
        $elementsCount = count($elements);
        
        foreach ($elements as $key => $value) {
            $row = $this->query->selectPageCommentDatabase("single", $value['id_reply']);
            
            $html .= "<li class=\"mdc-list-item\" data-comment=\"{$value['id']}\">";
                if (file_exists("{$this->utility->getPathWeb()}/files/user/{$value['username']}/Avatar.jpg") == true)
                    $html .= "<img class=\"mdc-list-item__graphic\" src=\"{$this->utility->getUrlRoot()}/files/user/{$value['username']}/Avatar.jpg\" aria-hidden=\"true\" alt=\"Avatar.jpg\"/>";
                else
                    $html .= "<img class=\"mdc-list-item__graphic\" src=\"{$this->utility->getUrlRoot()}/images/templates/{$setting['template']}/no_avatar.jpg\" aria-hidden=\"true\" alt=\"no_avatar.jpg\"/>";
                
                $detail = "";
                
                if (strpos($value['date_create'], "0000") === false && strpos($value['date_modify'], "0000") !== false) {
                    $dateFormat = $this->utility->dateFormat($value['date_create']);
                    
                    $detail = $this->utility->getTranslator()->trans("pageCommentController_7") . "{$dateFormat[0]} [{$dateFormat[1]}]";
                }
                else {
                    $dateFormat = $this->utility->dateFormat($value['date_modify']);
                    
                    $detail = $this->utility->getTranslator()->trans("pageCommentController_8") . "{$dateFormat[0]} [{$dateFormat[1]}]";
                }
                
                $quoteAvatar = "<img class=\"quote_avatar\" src=\"{$this->utility->getUrlRoot()}/images/templates/{$setting['template']}/no_avatar.jpg\" alt=\"no_avatar.jpg\"/>";
                
                if (file_exists("{$this->utility->getPathWeb()}/files/user/{$row['username']}/Avatar.jpg") == true)
                    $quoteAvatar = "<img class=\"quote_avatar\" src=\"{$this->utility->getUrlRoot()}/files/user/{$row['username']}/Avatar.jpg\" alt=\"Avatar.jpg\"/>";
                
                $html .= "<span class=\"mdc-list-item__text\">
                    <p class=\"detail\">$detail</p>";
                    
                    if ($row['argument'] != "")
                        $html .= "<p class=\"quote\">$quoteAvatar <span class=\"quote_text\">" . wordwrap($row['argument'], 50, "<br>\n", true) . "</span></p>";
                    
                    $html .= "<p class=\"mdc-list-item__secondary-text argument\">{$value['argument']}</p>
                </span>";
                
                if ($this->getUser() != null) {
                    if ($this->getUser()->getUsername() != $value['username']) {
                        $row = $this->query->selectPageCommentDatabase("reply", $value['id'], $this->getUser()->getUsername());
                        
                        if ($row == false)
                            $html .= "<span class=\"mdc-list-item__meta material-icons button_reply\">reply</span>";
                    }
                    else {
                        $row = $this->query->selectPageCommentDatabase("edit", $value['id']);
                        
                        if ($row == false)
                            $html .= "<span class=\"mdc-list-item__meta material-icons button_edit\">edit</span>";
                    }
                }

            $html .= "</li>";
            
            if ($key < $elementsCount - 1)
                $html .= "<li role=\"separator\" class=\"mdc-list-divider\"></li>";
        }
        
        $html .= "</ul>";
        
        return $html;
    }
    
    private function pageCommentDatabase($id, $argument) {
        $query = $this->utility->getConnection()->prepare("UPDATE pages_comments
                                                            SET argument = :argument,
                                                                date_modify = :dateModify
                                                            WHERE id = :id
                                                            AND username = :username");

        $query->bindValue(":argument", $argument);
        $query->bindValue(":dateModify", date("Y-m-d H:i:s"));
        $query->bindValue(":id", $id);
        $query->bindValue(":username", $this->getUser()->getUsername());

        return $query->execute();
    }
}