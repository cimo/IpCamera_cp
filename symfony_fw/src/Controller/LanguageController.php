<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;

use App\Form\LanguageFormType;

class LanguageController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $helper;
    private $query;
    private $ajax;
    
    private $session;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "language_text",
    *   path = "/language_text/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/module/language_text.html.twig")
    */
    public function textAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        $this->response['values']['languageRows'] = $this->query->selectAllLanguageDatabase();
        
        if ($request->isMethod("POST") == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $rootName = "/";
                
                if ($request->get("urlCurrentPageId") == 0)
                    $rootName = "/control_panel/";
                
                $this->response['values']['url'] = "{$this->helper->getUrlRoot()}$rootName{$this->urlLocale}/{$this->urlCurrentPageId}/{$this->urlExtra}";
            }
            else
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("languageController_1");
            
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
            'response' => $this->response
        );
    }
    
    /**
    * @Route(
    *   name = "language_page",
    *   path = "/language_page/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/module/language_page.html.twig")
    */
    public function pageAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        $settingRow = $this->helper->getSettingRow();
        
        $form = $this->createForm(LanguageFormType::class, null, Array(
            'validation_groups' => Array('language_code')
        ));
        $form->handleRequest($request);
        
        $this->response['values']['languageRows'] = $this->query->selectAllLanguageDatabase();
        $this->response['values']['settingRow'] = $this->query->selectSettingDatabase();
        
        foreach ($this->response['values']['languageRows'] as $key => $value) {
            $this->response['fileExists'][] = file_exists("{$this->helper->getPathPublic()}/images/templates/{$settingRow['template']}/lang/{$value['code']}.png");
        }
        
        if ($request->isMethod("POST") == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $codePage = $form->get("codePage")->getData();
                $pageRow = $this->query->selectPageDatabase($codePage, $this->session->get("pageProfileId"), true);
                
                $this->response['values']['codePage'] = $codePage;
                $this->response['values']['pageTitle'] = $pageRow['title'];
                $this->response['values']['pageArgument'] = html_entity_decode($pageRow['argument'], ENT_QUOTES, "UTF-8");
                $this->response['values']['pageMenuName'] = $pageRow['menu_name'];
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("languageController_2");
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
    
    // Functions private
}