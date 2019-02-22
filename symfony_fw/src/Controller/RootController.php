<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;
use App\Classes\System\Captcha;

class RootController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    private $captcha;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "root_render",
    *   path = "/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"GET", "POST"}
    * )
    * @Template("@templateRoot/render/index.html.twig")
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
        $this->captcha = new Captcha($this->utility);
        
        // Logic
        $_SESSION['currentPageId'] = $urlCurrentPageId;
        
        $this->response['path']['documentRoot'] = $_SERVER['DOCUMENT_ROOT'];
        $this->response['path']['root'] = $this->utility->getPathRoot();
        $this->response['path']['src'] = $this->utility->getPathSrc();
        $this->response['path']['public'] = $this->utility->getPathPublic();
        
        $this->response['url']['root'] = $this->utility->getUrlRoot();
        
        $this->response['modules']['left'] = $this->query->selectAllModuleDatabase(null, "left");
        $this->response['modules']['center'] = $this->query->selectAllModuleDatabase(null, "center");
        $this->response['modules']['right'] = $this->query->selectAllModuleDatabase(null, "right");
        
        if ($request->get("event") == "captchaImage") {
            $this->response['captchaImage'] = $this->captcha->create(7);
            
            return $this->ajax->response(Array(
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
    
    // Function private
}
