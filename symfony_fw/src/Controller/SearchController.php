<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
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
    
    private $utility;
    private $query;
    private $ajax;
    private $tableAndPagination;
    
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
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $form = $this->createForm(SearchFormType::class, null, Array(
            'validation_groups' => Array('search_module')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $words = $form->get("words")->getData();

                $this->response['values']['url'] = "{$this->utility->getUrlRoot()}{$this->utility->getWebsiteFile()}/{$this->urlLocale}/5/$words";
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("searchController_1");
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
        $pageRows = $this->query->selectAllPageDatabase($this->urlLocale, $this->urlExtra);
        
        if (empty($this->urlExtra) == true)
            $pageRows = Array();
        
        $tableAndPagination = $this->tableAndPagination->request($pageRows, 20, "searchRender", true, true);
        
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
            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("searchController_1");
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    // Functions private
    private function createListHtml($tableResult) {
        if (count($tableResult) == 0)
            return "";
        
        $listHtml = "<ul class=\"mdc-list mdc-list--two-line mdc-list--avatar-list\">";
        
        foreach ($tableResult as $key => $value) {      
            $listHtml .= "<li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">receipt</span>
                <span class=\"mdc-list-item__text\">
                    {$value['title']}
                    <span class=\"mdc-list-item__secondary-text\">";
                        $argument = preg_replace("/<(.*?)>/", " ", html_entity_decode($value['argument'], ENT_QUOTES, "UTF-8"));
                        
                        if (strlen($argument) > 200)
                            $listHtml .= substr($argument, 0, 200) . "...";
                        else
                            $listHtml .= $argument;
                    $listHtml .= "</span>
                </span>
                <a class=\"mdc-list-item__meta material-icons\" href=\"{$this->utility->getUrlRoot()}{$this->utility->getWebsiteFile()}/{$this->urlLocale}/{$value['id']}\">
                    info
                </a>
            </li>";
            
            if ($key < (count($tableResult) - 1))
                $listHtml .= "<li role=\"separator\" class=\"mdc-list-divider\"></li>";
        }
        
        $listHtml .= "</ul>";
        
        return $listHtml;
    }
}