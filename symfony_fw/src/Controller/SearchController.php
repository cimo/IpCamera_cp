<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Form\SearchFormType;

class SearchController extends AbstractController {
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
    *   name = "search_module",
    *   path = "/search_module/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/module/widget_search.html.twig")
    */
    public function moduleAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        $form = $this->createForm(SearchFormType::class, null, Array(
            'validation_groups' => Array("search_module")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $words = $form->get("words")->getData();

                $this->response['values']['url'] = "{$this->helper->getUrlRoot()}{$this->helper->getWebsiteFile()}/{$this->urlLocale}/5/$words";
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("searchController_1");
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
    *   name = "search_render",
    *   path = "/search_render/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/include/search.html.twig")
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
        
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale, $this->urlExtra);
        
        if (empty($this->urlExtra) == true)
            $pageRows = Array();
        
        $tableAndPagination = $this->tableAndPagination->request($pageRows, 20, "searchRender", true);
        
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
            $this->response['messages']['error'] = $this->helper->getTranslator()->trans("searchController_1");
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    // Functions private
    private function createListHtml($elements) {
        if (count($elements) == 0)
            return "";
        
        $listHtml = "<ul class=\"mdc-list mdc-list--two-line mdc-list--avatar-list\">";
        
        foreach ($elements as $key => $value) {
            $url = $this->get("router")->generate(
                "root_render",
                Array(
                    '_locale' => $this->urlLocale,
                    'urlCurrentPageId' => $value['id'],
                    'urlExtra' => ""
                )
            );

            $listHtml .= "<li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">receipt</span>
                <span class=\"mdc-list-item__text\">
                    {$value['title']}
                    <span class=\"mdc-list-item__secondary-text\">";
                        $argument = preg_replace("/<(.*?)>/", " ", html_entity_decode($value['argument'], ENT_QUOTES, "UTF-8"));
                        
                        if (strlen($argument) > 200)
                            $listHtml .= substr($argument, 0, 200) . "...";
                        else if (strlen($argument) == 0)
                            $listHtml .= "...";
                        else
                            $listHtml .= $argument;
                    $listHtml .= "</span>
                </span>
                <a class=\"mdc-list-item__meta material-icons\" href=\"{$url}\">
                    info
                </a>
            </li>";
            
            if ($key < (count($elements) - 1))
                $listHtml .= "<li role=\"separator\" class=\"mdc-list-divider\"></li>";
        }
        
        $listHtml .= "</ul>";
        
        return $listHtml;
    }
}