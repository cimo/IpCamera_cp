<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
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
    
    private $helper;
    private $query;
    private $ajax;
    private $tableAndPagination;
    
    private $session;
    
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($this->session->get("settingLinePushProfileId") == null && $request->get("id") == null)
            $settingLinePushEntity = new SettingLinePush();
        else {
            $id = $request->get("id") == null ? $this->session->get("settingLinePushProfileId") : $request->get("id");
            
            $this->session->set("settingLinePushProfileId", $id);
            
            $settingLinePushEntity = $this->entityManager->getRepository("App\Entity\SettingLinePush")->find($this->session->get("settingLinePushProfileId"));
        }
        
        $linePushNameOld = $settingLinePushEntity->getName();
        
        $this->settingLinePushList();
        
        $this->tableAndPaginationResult($settingLinePushEntity);
        
        $form = $this->createForm(SettingLinePushFormType::class, $settingLinePushEntity, Array(
            'validation_groups' => Array("setting_line_push")
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
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingLinePushController_3");
                
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
                if ($this->session->get("settingLinePushProfileId") != null)
                    $this->session->remove("settingLinePushProfileId");
                
                $this->entityManager->persist($settingLinePushEntity);
                $this->entityManager->flush();
                
                $this->settingLinePushList();
                
                $this->tableAndPaginationResult($settingLinePushEntity);
                
                $settingLinePushUserRows = $this->query->selectAllSettingLinePushUserDatabase("allPushName", $linePushNameOld);
                
                foreach ($settingLinePushUserRows as $key => $value) {
                    $this->query->updateLinePushUserDatabase(Array(
                        $settingLinePushEntity->getName(),
                        $value['user_id'],
                        "",
                        0
                    ));
                }
                
                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("settingLinePushController_1");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingLinePushController_2");
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? 0 : $request->get("id");
                    
                    $settingLinePushEntity = $this->entityManager->getRepository("App\Entity\SettingLinePush")->find($id);
                    
                    if ($settingLinePushEntity != null) {
                        if ($this->session->get("settingLinePushProfileId") != null)
                            $this->session->remove("settingLinePushProfileId");
                        
                        $this->query->deleteLinePushUserDatabase($settingLinePushEntity->getName());
                        
                        $this->entityManager->remove($settingLinePushEntity);
                        $this->entityManager->flush();
                        
                        $this->settingLinePushList();
                        
                        $this->tableAndPaginationResult(null);

                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("settingLinePushController_4");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingLinePushController_5");

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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "reset") {
                    $this->session->remove("settingLinePushProfileId");
                    
                    $this->tableAndPaginationResult(null);
                    
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("settingLinePushController_6");
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingLinePushController_7");
                
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
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
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
                        $settingLinePushRow = $this->query->selectSettingLinePushDatabase($messageTextExplode[0]);
                        
                        if ($settingLinePushRow != false)
                            $pushName = trim($messageTextExplode[0]);
                        
                        if (filter_var($messageTextExplode[1], FILTER_VALIDATE_EMAIL) !== false)
                            $pushEmail = trim($messageTextExplode[1]);
                    }
                }
                
                $settingLinePushUserRow = $this->query->selectSettingLinePushUserDatabase("userId", $source['userId']);
                
                if ($type == "follow" && $settingLinePushUserRow == false) {
                    $this->query->insertLinePushUserDatabase(Array(
                        "",
                        $source['userId'],
                        "",
                        0
                    ));
                }
                else if ($type == "message" && $settingLinePushUserRow != false) {
                    //$userRow = $this->query->selectUserDatabase($pushEmail);
                    
                    //if ($userRow != false) {
                        $this->query->updateLinePushUserDatabase(Array(
                            $pushName,
                            $source['userId'],
                            $pushEmail,
                            1
                        ));
                    //}
                }
                else if ($type == "unfollow" && $settingLinePushUserRow != false) {
                    $this->query->updateLinePushUserDatabase(Array(
                        "",
                        $source['userId'],
                        "",
                        0
                    ));
                }
                
                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("settingLinePushController_8");
            }
            else
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("settingLinePushController_9");
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
        
        $settingLinePushUserRows = $this->query->selectAllSettingLinePushUserDatabase("allPushName", $name);
        
        $tableAndPagination = $this->tableAndPagination->request($settingLinePushUserRows, 20, "pushUser", false);

        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
    }
    
    private function settingLinePushList() {
        $settingLinePushRows = $this->query->selectAllSettingLinePushDatabase();
        
        $this->response['values']['wordTagListHtml'] = $this->helper->createWordTagListHtml($settingLinePushRows);
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
                        
                        $isActive = $value['active'] == true ? $this->helper->getTranslator()->trans("settingLinePushController_10") : $this->helper->getTranslator()->trans("settingLinePushController_11");
                    
                        $listHtml .= "<b>Status:</b> $isActive
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