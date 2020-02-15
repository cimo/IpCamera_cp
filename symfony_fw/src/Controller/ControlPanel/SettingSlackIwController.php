<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;

use App\Entity\SettingSlackIw;
use App\Form\SettingSlackIwFormType;

class SettingSlackIwController extends AbstractController {
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
    *   name = "cp_setting_slack_iw_render",
    *   path = "/cp_setting_slack_iw_render/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/setting_slack_iw.html.twig")
    */
    public function renderAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        $this->settingSlackIwList();
        
        if ($this->session->get("settingSlackIwProfileId") == null && $request->get("id") == null)
            $settingSlackIwEntity = new SettingSlackIw();
        else {
            $id = $request->get("id") == null ? $this->session->get("settingSlackIwProfileId") : $request->get("id");
            
            $this->session->set("settingSlackIwProfileId", $id);
            
            $settingSlackIwEntity = $this->entityManager->getRepository("App\Entity\SettingSlackIw")->find($this->session->get("settingSlackIwProfileId"));
        }
        
        $form = $this->createForm(SettingSlackIwFormType::class, $settingSlackIwEntity, Array(
            'validation_groups' => Array('setting_slack_iw')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($request->get("event") == "profile") {
                if ($settingSlackIwEntity != null) {
                    $this->response['values']['entity'] = Array(
                        $settingSlackIwEntity->getName(),
                        $settingSlackIwEntity->getHook(),
                        $settingSlackIwEntity->getChannel(),
                        $settingSlackIwEntity->getActive()
                    );
                    
                    $this->settingSlackIwList();
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingSlackIwController_1");

                return $this->ajax->response(Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response
                ));
            }
            
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                if ($this->session->get("settingSlackIwProfileId") != null)
                    $this->session->remove("settingSlackIwProfileId");
                
                $this->entityManager->persist($settingSlackIwEntity);
                $this->entityManager->flush();
                
                $this->settingSlackIwList();

                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("settingSlackIwController_2");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingSlackIwController_3");
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
    *   name = "cp_setting_slack_iw_delete",
    *   path = "/cp_setting_slack_iw_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/setting_slack_iw.html.twig")
    */
    public function deleteAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? 0 : $request->get("id");
                    
                    $settingSlackIwEntity = $this->entityManager->getRepository("App\Entity\SettingSlackIw")->find($id);
                    
                    if ($settingSlackIwEntity != null) {
                        if ($this->session->get("settingSlackIwProfileId") != null)
                            $this->session->remove("settingSlackIwProfileId");
                        
                        $this->entityManager->remove($settingSlackIwEntity);
                        $this->entityManager->flush();
                        
                        $this->settingSlackIwList();

                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("settingSlackIwController_4");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingSlackIwController_5");

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
    
    /**
    * @Route(
    *   name = "cp_setting_slack_iw_reset",
    *   path = "/cp_setting_slack_iw_reset/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/setting_slack_iw.html.twig")
    */
    public function resetAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "reset") {
                    $this->session->remove("settingSlackIwProfileId");
                    
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("settingSlackIwController_6");
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingSlackIwController_7");
                
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
    private function settingSlackIwList() {
        $rows = $this->query->selectAllSettingSlackIwDatabase();
        
        $this->response['values']['wordTagListHtml'] = $this->helper->createWordTagListHtml($rows);
    }
}