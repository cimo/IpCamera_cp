<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
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
    
    private $helper;
    private $query;
    private $ajax;
    private $tableAndPagination;
    
    private $session;
    
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
        
        $settingRow = $this->helper->getSettingRow();
        
        if ($settingRow['pageComment'] == true) {
            $pageCommentRows = $this->query->selectAllPageCommentDatabase($this->urlCurrentPageId);

            $tableAndPagination = $this->tableAndPagination->request($pageCommentRows, 20, "pageComment", false);

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
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("pageCommentController_1");

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
        
        $settingRow = $this->helper->getSettingRow();
        $pageRow = $this->query->selectPageDatabase($this->urlLocale, $this->urlCurrentPageId, true);
        
        if ($settingRow['pageComment'] == true && $settingRow['pageComment_active'] == true && $pageRow['comment'] == true) {
            $pageCommentEntity = new PageComment();

            $form = $this->createForm(PageCommentFormType::class, $pageCommentEntity, Array(
                'validation_groups' => Array("page_comment")
            ));
            $form->handleRequest($request);

            if ($request->isMethod("POST") == true) {
                if ($form->isSubmitted() == true && $form->isValid() == true) {
                    $pageCommentRows = $this->query->selectAllPageCommentDatabase($this->urlCurrentPageId);
                    
                    $typeExplode = explode("_", $form->get("type")->getData());
                    
                    $argument = $this->helper->escapeScript($form->get("argument")->getData());
                    
                    if ($typeExplode[0] == "new") {
                        if ($pageCommentRows[count($pageCommentRows) - 1]['username'] !== $this->getUser()->getUsername()) {
                            $pageCommentEntity->setPageId($this->urlCurrentPageId);
                            $pageCommentEntity->setUsername($this->getUser()->getUsername());
                            $pageCommentEntity->setArgument($argument);
                            $pageCommentEntity->setDateCreate($this->helper->dateFormat());
                            
                            $this->entityManager->persist($pageCommentEntity);
                            $this->entityManager->flush();

                            $this->response['messages']['success'] = $this->helper->getTranslator()->trans("pageCommentController_2");
                        }
                        else
                            $this->response['messages']['success'] = $this->helper->getTranslator()->trans("pageCommentController_3");
                    }
                    else if ($typeExplode[0] == "reply") {
                        $pageCommentRow = $this->query->selectPageCommentDatabase("single", $typeExplode[1]);

                        if ($pageCommentRow != false) {
                            $pageCommentEntity->setPageId($this->urlCurrentPageId);
                            $pageCommentEntity->setUsername($this->getUser()->getUsername());
                            $pageCommentEntity->setIdReply($typeExplode[1]);
                            $pageCommentEntity->setArgument($argument);
                            $pageCommentEntity->setDateCreate($this->helper->dateFormat());
                            
                            $this->entityManager->persist($pageCommentEntity);
                            $this->entityManager->flush();

                            $this->response['messages']['success'] = $this->helper->getTranslator()->trans("pageCommentController_4");
                        }
                    }
                    else if ($typeExplode[0] == "edit") {
                        $this->query->updatePageCommentDatabase($argument, $typeExplode[1], $this->getUser());

                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("pageCommentController_5");
                    }
                    else
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("pageCommentController_6");
                }
                else {
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("pageCommentController_6");
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
        if (count($elements) == 0)
            return "";
        
        $settingRow = $this->query->selectSettingDatabase();
        
        $listHtml = "<ul class=\"mdc-list mdc-list--two-line mdc-list--avatar-list\">";
        
        $elementsCount = count($elements);
        
        foreach ($elements as $key => $value) {
            $pageCommentRow = $this->query->selectPageCommentDatabase("single", $value['id_reply']);
            
            $userRow = $this->query->selectUserDatabase($value['username']);
            
            $listHtml .= "<li class=\"mdc-list-item\" data-comment=\"{$value['id']}\">";
                if ($userRow['image'] != null && file_exists("{$this->helper->getPathPublic()}/files/user/{$value['username']}/{$userRow['image']}") == true)
                    $listHtml .= "<img class=\"mdc-list-item__graphic\" src=\"{$this->helper->getUrlRoot()}/files/user/{$value['username']}/{$userRow['image']}\" aria-hidden=\"true\" alt=\"{$userRow['image']}\"/>";
                else
                    $listHtml .= "<img class=\"mdc-list-item__graphic\" src=\"{$this->helper->getUrlRoot()}/images/templates/{$settingRow['template']}/no_avatar.png\" aria-hidden=\"true\" alt=\"no_avatar.png\"/>";
                
                $detail = "";
                
                if (strpos($value['date_create'], "0000") === false && strpos($value['date_modify'], "0000") !== false)
                    $detail = "{$this->helper->getTranslator()->trans("pageCommentController_7")} {$this->helper->dateFormat($value['date_create'])}";
                else
                    $detail = "{$this->helper->getTranslator()->trans("pageCommentController_8")} {$this->helper->dateFormat($value['date_modify'])}";
                
                $quoteAvatar = "<img class=\"quote_avatar\" src=\"{$this->helper->getUrlRoot()}/images/templates/{$settingRow['template']}/no_avatar.png\" alt=\"no_avatar.png\"/>";
                
                if ($userRow['image'] != null && file_exists("{$this->helper->getPathPublic()}/files/user/{$pageCommentRow['username']}/{$userRow['image']}") == true)
                    $quoteAvatar = "<img class=\"quote_avatar\" src=\"{$this->helper->getUrlRoot()}/files/user/{$pageCommentRow['username']}/{$userRow['image']}\" alt=\"{$userRow['image']}\"/>";
                
                $listHtml .= "<span class=\"mdc-list-item__text\">
                    <p class=\"detail\">{$detail}</p>";
                    
                    $argument = base64_decode($pageCommentRow['argument']);
                    
                    if ($argument != "")
                        $listHtml .= "<p class=\"quote\">$quoteAvatar <span class=\"quote_text\">" . wordwrap($argument, 50, "<br>\n", true) . "</span></p>";
                    
                    $listHtml .= "<p class=\"mdc-list-item__secondary-text argument\">" . base64_decode($value['argument']) . "</p>
                </span>";
                
                if ($this->getUser() != null) {
                    if ($this->getUser()->getUsername() != $value['username']) {
                        $pageCommentReplyRow = $this->query->selectPageCommentDatabase("reply", $value['id'], $this->getUser()->getUsername());
                        
                        if ($pageCommentReplyRow == false)
                            $listHtml .= "<span class=\"mdc-list-item__meta material-icons button_reply\">reply</span>";
                    }
                    else {
                        $pageCommentEditRow = $this->query->selectPageCommentDatabase("edit", $value['id']);
                        
                        if ($pageCommentEditRow == false)
                            $listHtml .= "<span class=\"mdc-list-item__meta material-icons button_edit\">edit</span>";
                    }
                }

            $listHtml .= "</li>";
            
            if ($key < $elementsCount - 1)
                $listHtml .= "<li role=\"separator\" class=\"mdc-list-divider\"></li>";
        }
        
        $listHtml .= "</ul>";
        
        return $listHtml;
    }
}