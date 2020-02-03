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
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        $settingEntity = $this->entityManager->getRepository("App\Entity\Setting")->find(1);
        
        $templateColumn = $settingEntity->getTemplateColumn();
        $https = $settingEntity->getHttps();
        
        $languageCustomData = $this->languageCustomData();
        
        $form = $this->createForm(SettingFormType::class, $settingEntity, Array(
            'validation_groups' => Array('setting'),
            'choicesTemplate' => $this->helper->createTemplateList(),
            'choicesLanguage' => array_column($languageCustomData, "value", "text")
        ));
        $form->handleRequest($request);
        
        $this->response['values']['userRoleSelectHtml'] = $this->helper->createUserRoleSelectHtml("form_setting_roleUserId_select", "settingController_1", true);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                if ($form->get("templateColumn")->getData() != $templateColumn)
                    $this->moduleDatabase($form->get("templateColumn")->getData());
                
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
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
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
                            $settingDatabase = $this->settingDatabase("insertLanguage", $code, $date, $active);
                        else if ($request->get("event") == "modifyLanguage" && ($code !== $settingRow['language'] || $code === $settingRow['language'] && $active == 1))
                            $settingDatabase = $this->settingDatabase("updateLanguage", $code, $date, $active);
                        
                        if ($settingDatabase == true) {
                            if ($request->get("event") == "createLanguage") {
                                touch("{$this->helper->getPathRoot()}/translations/messages.$code.yml");

                                $this->settingDatabase("insertLanguageInPage", $code);

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
                        $settingDatabase = $this->settingDatabase("deleteLanguage", $code);

                        if ($settingDatabase == true) {
                            unlink("{$this->helper->getPathRoot()}/translations/messages.$code.yml");

                            $this->settingDatabase("deleteLanguageInPage", $code);
                            
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
            $customData[$key]['text'] = "{$value['code']} | {$value['date']} | $active";
        }
        
        return $customData;
    }
    
    private function moduleDatabase($templateColumn) {
        if ($templateColumn == 1) {
            $query = $this->helper->getConnection()->prepare("UPDATE module
                                                                SET position_tmp = :positionTmp,
                                                                    position = :position
                                                                WHERE position_tmp = :position");
            
            
            $query->bindValue(":positionTmp", "");
            $query->bindValue(":position", "right");
            
            $query->execute();
            
            $query->bindValue(":positionTmp", "");
            $query->bindValue(":position", "left");
            
            $query->execute();
        }
        else if ($templateColumn == 2 || $templateColumn == 3 || $templateColumn == 4) {
            $query = $this->helper->getConnection()->prepare("UPDATE module
                                                                SET position_tmp = :positionTmp,
                                                                    position = :position
                                                                WHERE position = :positionTmp");
            
            if ($templateColumn == 2) {
                $query->bindValue(":positionTmp", "right");
                $query->bindValue(":position", "center");
                
                $query->execute();
                
                $query = $this->helper->getConnection()->prepare("UPDATE module
                                                                    SET position_tmp = :positionTmp,
                                                                        position = :position
                                                                    WHERE position_tmp = :position");
                
                $query->bindValue(":positionTmp", "");
                $query->bindValue(":position", "left");
                
                $query->execute();
            }
            else if ($templateColumn == 3) {
                $query->bindValue(":positionTmp", "left");
                $query->bindValue(":position", "center");
                
                $query->execute();
                
                $query = $this->helper->getConnection()->prepare("UPDATE module
                                                                    SET position_tmp = :positionTmp,
                                                                        position = :position
                                                                    WHERE position_tmp = :position");
                
                $query->bindValue(":positionTmp", "");
                $query->bindValue(":position", "right");
                
                $query->execute();
            }
            else if ($templateColumn == 4) {
                $query->bindValue(":positionTmp", "right");
                $query->bindValue(":position", "center");

                $query->execute();
                
                $query->bindValue(":positionTmp", "left");
                $query->bindValue(":position", "center");
                
                $query->execute();
            }
        }
        
        $this->updateModuleRankInColumn("left");
        $this->updateModuleRankInColumn("center");
        $this->updateModuleRankInColumn("right");
    }
    
    private function updateModuleRankInColumn($position) {
        $moduleRows = $this->query->selectAllModuleDatabase(null, $position);
        
        foreach($moduleRows as $key => $value) {
            $query = $this->helper->getConnection()->prepare("UPDATE module
                                                                SET rank_in_column = :rankInColumn
                                                                WHERE id = :id");
            
            $query->bindValue(":rankInColumn", $key + 1);
            $query->bindValue(":id", $value['id']);
            
            $query->execute();
        }
    }
    
    private function settingDatabase($type, $code, $date = null, $active = 0) {
        if ($type == "insertLanguage") {
            $query = $this->helper->getConnection()->prepare("INSERT INTO language (code, date, active)
                                                                VALUES (:code, :date, :active)");
            
            $query->bindValue(":code", $code);
            $query->bindValue(":date", $date);
            $query->bindValue(":active", $active);
            
            return $query->execute();
        }
        else if ($type == "updateLanguage") {
            $query = $this->helper->getConnection()->prepare("UPDATE language
                                                                SET date = :date,
                                                                    active = :active
                                                                WHERE code = :code");
            
            $query->bindValue(":date", $date);
            $query->bindValue(":active", $active);
            $query->bindValue(":code", $code);
            
            return $query->execute();
        }
        else if ($type == "deleteLanguage") {
            $query = $this->helper->getConnection()->prepare("DELETE FROM language
                                                                WHERE code = :code");
            
            $query->bindValue(":code", $code);

            return $query->execute();
        }
        else if ($type == "insertLanguageInPage") {
            $codeTmp = is_string($code) == true ? $code : "";
            $codeTmp = strlen($codeTmp) == true ? $codeTmp : "";
            $codeTmp = ctype_alpha($codeTmp) == true ? $codeTmp : "";
            
            $query = $this->helper->getConnection()->prepare("ALTER TABLE page_title ADD $codeTmp VARCHAR(255) DEFAULT '';
                                                                ALTER TABLE page_argument ADD $codeTmp LONGTEXT;
                                                                ALTER TABLE page_menu_name ADD $codeTmp VARCHAR(255) NOT NULL DEFAULT '-';");
            
            $query->execute();
        }
        else if ($type == "deleteLanguageInPage") {
            $codeTmp = is_string($code) == true ? $code : "";
            $codeTmp = strlen($codeTmp) == true ? $codeTmp : "";
            $codeTmp = ctype_alpha($codeTmp) == true ? $codeTmp : "";
            
            $query = $this->helper->getConnection()->prepare("ALTER TABLE page_title DROP $codeTmp;
                                                                ALTER TABLE page_argument DROP $codeTmp;
                                                                ALTER TABLE page_menu_name DROP $codeTmp;");
            
            $query->execute();
        }
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