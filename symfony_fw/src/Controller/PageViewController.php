<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;

class PageViewController extends AbstractController {
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
    * @Template("@templateRoot/render/module/page_view.html.twig")
    */
    public function moduleAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        $moduleEntity = $this->entityManager->getRepository("App\Entity\Module")->find(2);
        
        $this->response['module']['id'] = $moduleEntity->getId();
        $this->response['module']['label'] = $moduleEntity->getLabel();
        
        $this->response['values']['controllerAction'] = null;
        $this->response['values']['title'] = $this->helper->getTranslator()->trans("pageViewController_1");
        $this->response['values']['argument'] = $this->helper->getTranslator()->trans("pageViewController_2");
        $this->response['values']['comment'] = false;
        $this->response['values']['userCreate'] = "-";
        $this->response['values']['dateCreate'] = "-";
        $this->response['values']['userModify'] = "-";
        $this->response['values']['dateModify'] = "-";
        
        $pageRow = $this->query->selectPageDatabase($this->urlLocale, $this->urlCurrentPageId);
        
        if ($pageRow != false) {
            if ($pageRow['only_parent'] == true)
                return $this->pageDoesNotExist();
            
            $this->response['values']['controllerAction'] = $pageRow['controller_action'];
            $this->response['values']['title'] = $pageRow['title'];
            $this->response['values']['argument'] = html_entity_decode($pageRow['argument'], ENT_QUOTES, "UTF-8");
            $this->response['values']['comment'] = $pageRow['comment'];
            $this->response['values']['userCreate'] = $pageRow['user_create'];
            $this->response['values']['dateCreate'] = strpos($pageRow['date_create'], "0000") !== false ? "-" : $this->helper->dateFormat($pageRow['date_create']);
            $this->response['values']['userModify'] = $pageRow['user_modify'];
            $this->response['values']['dateModify'] = strpos($pageRow['date_modify'], "0000") !== false ? "-" : $this->helper->dateFormat($pageRow['date_modify']);
            
            if ($this->helper->getAuthorizationChecker()->isGranted("IS_AUTHENTICATED_FULLY") == true) {
                if ($pageRow['protected'] == false && ($pageRow['id'] == 3 || $pageRow['id'] == 4)) {
                    // Page not available with login
                    $this->response['values']['controllerAction'] = null;
                    $this->response['values']['argument'] = $this->helper->getTranslator()->trans("pageViewController_3");

                    return Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $pageRow['id'],
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    );
                }
                else if ($pageRow['protected'] == true) {
                    $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
                    
                    if ($checkUserRole == true) {
                        $arrayExplodeFindValue = $this->helper->arrayExplodeFindValue($pageRow['role_user_id'], $this->getUser()->getRoleUserId());

                        if ($arrayExplodeFindValue == false) {
                            // Page not available for role
                            $this->response['values']['controllerAction'] = null;
                            $this->response['values']['argument'] = $this->helper->getTranslator()->trans("pageViewController_4");

                            return Array(
                                'urlLocale' => $this->urlLocale,
                                'urlCurrentPageId' => $pageRow['id'],
                                'urlExtra' => $this->urlExtra,
                                'response' => $this->response
                            );
                        }
                        else {
                            // Page normal
                            return Array(
                                'urlLocale' => $this->urlLocale,
                                'urlCurrentPageId' => $pageRow['id'],
                                'urlExtra' => $this->urlExtra,
                                'response' => $this->response
                            );
                        }
                    }
                    else {
                        // Page normal
                        return Array(
                            'urlLocale' => $this->urlLocale,
                            'urlCurrentPageId' => $pageRow['id'],
                            'urlExtra' => $this->urlExtra,
                            'response' => $this->response
                        );
                    }
                }
                else {
                    // Page normal
                    return Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $pageRow['id'],
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    );
                }
            }
            else {
                $settingRow = $this->helper->getSettingRow();
                
                if ($pageRow['id'] == 3 && $settingRow['registration'] == false) {
                    $this->response['values']['controllerAction'] = null;
                    $this->response['values']['argument'] = $this->helper->getTranslator()->trans("pageViewController_5");
                    
                    // Page registration disable
                    return Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $pageRow['id'],
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    );
                }
                else if ($pageRow['id'] == 4 && $settingRow['recover_password'] == false) {
                    $this->response['values']['controllerAction'] = null;
                    $this->response['values']['argument'] = $this->helper->getTranslator()->trans("pageViewController_6");
                    
                    // Page recover password disable
                    return Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $pageRow['id'],
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    );
                }
                else if ($pageRow['protected'] == false) {
                    // Page normal
                    return Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $pageRow['id'],
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    );
                }
                else {
                    // Page accessible only with login
                    $this->response['values']['controllerAction'] = null;
                    $this->response['values']['argument'] = $this->helper->getTranslator()->trans("pageViewController_7");
                    
                    return Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $pageRow['id'],
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    );
                }
            }
            
            return $this->pageDoesNotExist();
        }
        else
            return $this->pageDoesNotExist();
    }
    
    // Functions private
    private function pageDoesNotExist() {
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => 0,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
}