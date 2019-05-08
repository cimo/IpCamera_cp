<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Entity\SettingLinePush;
use App\Form\SettingLinePushFormType;

class SettingLinePushController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_setting_line_push_render",
    *   path = "/cp_setting_line_push_render/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/setting_line_push.html.twig")
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
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if (isset($_SESSION['settingLinePushProfileId']) == false && $request->get("id") == null)
            $settingLinePushEntity = new SettingLinePush();
        else {
            $id = $request->get("id") == null ? $_SESSION['settingLinePushProfileId'] : $request->get("id");
            
            $_SESSION['settingLinePushProfileId'] = $id;
            
            $settingLinePushEntity = $this->entityManager->getRepository("App\Entity\SettingLinePush")->find($_SESSION['settingLinePushProfileId']);
        }
        
        $linePushNameOld = $settingLinePushEntity->getName();
        
        $this->settingLinePushList();
        
        $this->tableAndPaginationResult($settingLinePushEntity);
        
        $form = $this->createForm(SettingLinePushFormType::class, $settingLinePushEntity, Array(
            'validation_groups' => Array('setting_line_push')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($request->get("event") == "profile") {
                if ($settingLinePushEntity != null) {
                    $this->response['values']['entity'] = Array(
                        $settingLinePushEntity->getName(),
                        $settingLinePushEntity->getUserIdPrimary(),
                        $settingLinePushEntity->getAccessToken(),
                        $settingLinePushEntity->getActive()
                    );
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingLinePushController_3");
                
                return $this->ajax->response(Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response
                ));
            }
            else if ($request->get("event") == "tableAndPagination") {
                return $this->ajax->response(Array(
                    'urlLocale' => $this->urlLocale,
                    'urlCurrentPageId' => $this->urlCurrentPageId,
                    'urlExtra' => $this->urlExtra,
                    'response' => $this->response
                ));
            }
            
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                if (isset($_SESSION['settingLinePushProfileId']) == true)
                    unset($_SESSION['settingLinePushProfileId']);
                
                $this->entityManager->persist($settingLinePushEntity);
                $this->entityManager->flush();
                
                $this->settingLinePushList();
                
                $this->tableAndPaginationResult($settingLinePushEntity);
                
                $pushUserRows = $this->query->selectAllSettingLinePushUserDatabase("allPushName", $linePushNameOld);
                
                foreach ($pushUserRows as $key => $value) {
                    $this->linePushUserDatabase("update", Array(
                        $settingLinePushEntity->getName(),
                        $value['user_id'],
                        "",
                        0
                    ));
                }
                
                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingLinePushController_1");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingLinePushController_2");
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
    *   name = "cp_setting_line_push_delete",
    *   path = "/cp_setting_line_push_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/setting_line_push.html.twig")
    */
    public function deleteAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") != null ? $request->get("id") : 0;
                    
                    $settingLinePushEntity = $this->entityManager->getRepository("App\Entity\SettingLinePush")->find($id);
                    
                    if ($settingLinePushEntity != null) {
                        if (isset($_SESSION['settingLinePushProfileId']) == true)
                            unset($_SESSION['settingLinePushProfileId']);
                        
                        $this->linePushUserDatabase("delete", $settingLinePushEntity->getName());
                        
                        $this->entityManager->remove($settingLinePushEntity);
                        $this->entityManager->flush();
                        
                        $this->settingLinePushList();
                        
                        $this->tableAndPaginationResult(null);

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingLinePushController_4");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingLinePushController_5");

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
    *   name = "cp_setting_line_push_reset",
    *   path = "/cp_setting_line_push_reset/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/setting_line_push.html.twig")
    */
    public function resetAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "reset") {
                    unset($_SESSION['settingLinePushProfileId']);
                    
                    $this->tableAndPaginationResult(null);
                    
                    $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingLinePushController_6");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingLinePushController_7");
                
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
    *   name = "cp_setting_line_push_webhook",
    *   path = "/cp_setting_line_push_webhook",
    *	methods={"POST", "OPTIONS"}
    * )
    */
    public function requestWebhookAction(Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        if ($request->isMethod("POST") == true) {
            $parameters = Array();

            if (empty($request->getContent()) == false)
                $parameters = json_decode($request->getContent(), true);
            else
                $parameters = $request->request->all();
            
            $this->parameters = $parameters;
            
            if (isset($this->parameters['events'][0]['type']) == true) {
                $type = isset($this->parameters['events'][0]['type']) == true ? $this->parameters['events'][0]['type'] : "";
                $source = isset($this->parameters['events'][0]['source']) == true ? $this->parameters['events'][0]['source'] : "";
                $message = isset($this->parameters['events'][0]['message']) == true ? $this->parameters['events'][0]['message'] : "";
                
                $pushName = "";
                $pushEmail = "";
                
                if (isset($message['text']) == true) {
                    $messageTextExplode = explode("/", $message['text']);
                    
                    if (count($messageTextExplode) == 2) {
                        $pushRow = $this->query->selectSettingLinePushDatabase($messageTextExplode[0]);
                        
                        if ($pushRow != false)
                            $pushName = trim($messageTextExplode[0]);
                        
                        if (filter_var($messageTextExplode[1], FILTER_VALIDATE_EMAIL) !== false)
                            $pushEmail = trim($messageTextExplode[1]);
                    }
                }
                
                $pushUserRow = $this->query->selectSettingLinePushUserDatabase("userId", $source['userId']);
                
                if ($type == "follow" && $pushUserRow == false) {
                    $this->linePushUserDatabase("insert", Array(
                        "",
                        $source['userId'],
                        "",
                        0
                    ));
                }
                else if ($type == "message" && $pushUserRow != false) {
                    //$userRow = $this->query->selectUserDatabase($pushEmail);
                    
                    //if ($userRow != false) {
                        $this->linePushUserDatabase("update", Array(
                            $pushName,
                            $source['userId'],
                            $pushEmail,
                            1
                        ));
                    //}
                }
                else if ($type == "unfollow" && $pushUserRow != false) {
                    $this->linePushUserDatabase("update", Array(
                        "",
                        $source['userId'],
                        "",
                        0
                    ));
                }
                
                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("settingLinePushController_8");
            }
            else
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("settingLinePushController_9");
        }
        
        $response = new Response(json_encode($this->response));
        $response->headers->set("Access-Control-Allow-Origin", "*");
        $response->headers->set("Access-Control-Allow-Headers", "*");
        $response->headers->set("Access-Control-Allow-Methods", "POST, OPTIONS");
        $response->headers->set("Accept", "application/json");
        $response->headers->set("Content-Type", "application/json");
        
        return $response;
    }
    
    // Functions private
    private function tableAndPaginationResult($settingLinePushEntity) {
        $name = "";
        
        if ($settingLinePushEntity != null)
            $name = $settingLinePushEntity->getName();
        
        $pushUserRows = $this->query->selectAllSettingLinePushUserDatabase("allPushName", $name);
        
        $tableAndPagination = $this->tableAndPagination->request($pushUserRows, 20, "pushUser", true);

        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
    }
    
    private function settingLinePushList() {
        $rows = $this->query->selectAllSettingLinePushDatabase();
        
        $this->response['values']['wordTagListHtml'] = $this->utility->createWordTagListHtml($rows);
    }
    
    private function linePushUserDatabase($type, $elements) {
        if ($type == "insert") {
            $query = $this->utility->getConnection()->prepare("INSERT INTO setting_line_push_user (
                                                                    push_name,
                                                                    user_id,
                                                                    email,
                                                                    active
                                                                )
                                                                VALUES (
                                                                    :pushName,
                                                                    :userId,
                                                                    :email,
                                                                    :active
                                                                );");
            
            $query->bindValue(":pushName", $elements[0]);
            $query->bindValue(":userId", $elements[1]);
            $query->bindValue(":email", $elements[2]);
            $query->bindValue(":active", $elements[3]);
        }
        else if ($type == "update") {
            if ($elements[0] != "" && $elements[1] != "" && $elements[2] != "") {
                $query = $this->utility->getConnection()->prepare("UPDATE setting_line_push_user
                                                                    SET push_name = :pushName,
                                                                        email = :email,
                                                                        active = :active
                                                                    WHERE user_id = :userId");

                $query->bindValue(":pushName", $elements[0]);
                $query->bindValue(":email", $elements[2]);
                $query->bindValue(":active", $elements[3]);
                $query->bindValue(":userId", $elements[1]);
            }
            else if ($elements[0] != "" && $elements[1] != "" && $elements[2] == "") {
                $query = $this->utility->getConnection()->prepare("UPDATE setting_line_push_user
                                                                    SET push_name = :pushName
                                                                    WHERE user_id = :userId");

                $query->bindValue(":pushName", $elements[0]);
                $query->bindValue(":userId", $elements[1]);
            }
            else if ($elements[0] == "" && $elements[1] != "" && $elements[2] == "") {
                $query = $this->utility->getConnection()->prepare("UPDATE setting_line_push_user
                                                                    SET active = :active
                                                                    WHERE user_id = :userId");
                
                $query->bindValue(":active", $elements[3]);
                $query->bindValue(":userId", $elements[1]);
            }
        }
        else if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM setting_line_push_user
                                                                WHERE push_name = :pushName");
            
            $query->bindValue(":pushName", $elements);
        }
        
        return $query->execute();
    }
    
    private function createListHtml($elements) {
        $listHtml = "<ul class=\"mdc-list mdc-list--two-line mdc-list--avatar-list cp_line_push_user\">";
        
        $elementsCount = count($elements);
        
        foreach ($elements as $key => $value) {
            $listHtml .= "<li class=\"mdc-list-item\" data-comment=\"{$value['id']}\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    <span class=\"mdc-list-item__secondary-text\">
                        <b>Id:</b> {$value['user_id']}<br>
                        <b>Email:</b> {$value['email']}<br>";
                        
                        $isActive = $value['active'] == true ? $this->utility->getTranslator()->trans("settingLinePushController_10") : $this->utility->getTranslator()->trans("settingLinePushController_11");
                    
                        $listHtml .= "$isActive
                    </span>
                </span>
            </li>";
            
            if ($key < $elementsCount - 1)
                $listHtml .= "<li role=\"separator\" class=\"mdc-list-divider\"></li>";
        }
        
        $listHtml .= "</ul>";
        
        if (count($elements) == 0)
            $listHtml = "";
        
        return $listHtml;
    }
}