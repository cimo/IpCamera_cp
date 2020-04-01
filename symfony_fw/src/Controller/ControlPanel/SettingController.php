<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;

use App\Form\SettingFormType;

class SettingController extends AbstractController {
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
    *   name = "cp_setting_save",
    *   path = "/cp_setting_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/setting.html.twig")
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        $settingEntity = $this->entityManager->getRepository("App\Entity\Setting")->find(1);
        
        $templateColumn = $settingEntity->getTemplateColumn();
        $https = $settingEntity->getHttps();
        
        $languageCustomData = $this->languageCustomData();
        
        $form = $this->createForm(SettingFormType::class, $settingEntity, Array(
            'validation_groups' => Array("setting"),
            'template' => $this->helper->createTemplateList(),
            'language' => array_column($languageCustomData, "value", "text")
        ));
        $form->handleRequest($request);
        
        $this->response['values']['userRoleSelectHtml'] = $this->helper->createUserRoleSelectHtml("form_setting_roleUserId_select", "settingController_1", true);

        $this->response['values']['serverRoot'] = $_SERVER['DOCUMENT_ROOT'] != $settingEntity->getServerRoot() ? $_SERVER['DOCUMENT_ROOT'] : "";
        $this->response['values']['serverHost'] = $_SERVER['HTTP_HOST'] != $settingEntity->getServerHost() ? $_SERVER['HTTP_HOST'] : "";
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                if ($form->get("templateColumn")->getData() != $templateColumn)
                    $this->query->updateModuleDatabase($form->get("templateColumn")->getData());
                
                $this->entityManager->persist($settingEntity);
                $this->entityManager->flush();
                    
                if ($form->get("https")->getData() != $https) {
                    $message = $this->helper->getTranslator()->trans("settingController_2");
                    
                    $this->session->set("userInform", $message);
                    
                    $this->response['messages']['info'] = $message;
                    
                    return $this->helper->forceLogout($this->router);
                }
                else
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("settingController_3");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingController_4");
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
    *   name = "cp_setting_language_manage",
    *   path = "/cp_setting_language_manage/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/setting.html.twig")
    */
    public function languageManageAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $settingRow = $this->helper->getSettingRow();
                
                if ($request->get("event") == "createLanguage" || $request->get("event") == "modifyLanguage") {
                    $code = $request->get("code");
                    $date = $request->get("date");
                    $active = $request->get("active");

                    $languageRows = $this->query->selectAllLanguageDatabase();

                    $exists = false;
                    $checked = false;

                    if ($request->get("event") == "createLanguage") {
                        foreach ($languageRows as $key => $value) {
                            if ($code == $value['code']) {
                                $exists = true;

                                break;
                            }
                        }
                    }

                    if (strtolower($date) == "y-m-d" || strtolower($date) == "d-m-y" || strtolower($date) == "m-d-y")
                        $checked = true;

                    if ($code != "" && $date != "" && $active != "" && $exists == false && $checked == true) {
                        $settingDatabase = false;
                        
                        if ($request->get("event") == "createLanguage")
                            $settingDatabase = $this->query->insertLanguageDatabase("text", $code, $date, $active);
                        else if ($request->get("event") == "modifyLanguage" && ($code != $settingRow['language'] || $code == $settingRow['language'] && $active == 1))
                            $settingDatabase = $this->query->updateLanguageDatabase($date, $active, $code);
                        
                        if ($settingDatabase == true) {
                            if ($request->get("event") == "createLanguage") {
                                touch("{$this->helper->getPathRoot()}/translations/messages.{$code}.yml");
                                
                                $this->query->insertLanguageDatabase("page", $code);
                                
                                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("settingController_5");
                            }
                            else if ($request->get("event") == "modifyLanguage") {
                                if ($code == $request->getLocale())
                                    $this->response['values']['url'] = $this->redirectOnModifySelected($settingRow);
                                else
                                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("settingController_6");
                            }
                        }
                        else
                            $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingController_7");
                    }
                    else
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingController_7");
                }
                else if ($request->get("event") == "deleteLanguage") {
                    $code = $request->get("code");
                    
                    if ($code !== $settingRow['language']) {
                        $settingDatabase = $this->query->deleteLanguageDatabase("text", $code);

                        if ($settingDatabase == true) {
                            unlink("{$this->helper->getPathRoot()}/translations/messages.$code.yml");

                            $this->query->deleteLanguageDatabase("page", $code);
                            
                            if ($code == $request->getLocale())
                                $this->response['values']['url'] = $this->redirectOnModifySelected($settingRow);
                            else
                                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("settingController_8");
                        }
                        else
                            $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingController_9");
                    }
                    else
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingController_9");
                }

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
    private function languageCustomData() {
        $languageRows = $this->query->selectAllLanguageDatabase();
        
        $customData = Array();
        
        foreach($languageRows as $key => $value) {
            $active = $value['active'] == 1 ? $this->helper->getTranslator()->trans("settingController_10") : $this->helper->getTranslator()->trans("settingController_11");
            
            $customData[$key]['value'] = $value['code'];
            $customData[$key]['text'] = "{$value['code']} | {$value['date']} | {$active}";
        }
        
        return $customData;
    }
    
    private function redirectOnModifySelected($settingRow) {
        $url = $this->get("router")->generate(
            "root_render",
            Array(
                '_locale' => $settingRow['language'],
                'urlCurrentPageId' => 2,
                'urlExtra' => ""
            )
        );
        
        return $url;
    }
}