<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Entity\MicroserviceDeploy;
use App\Form\MicroserviceDeployFormType;
use App\Form\MicroserviceDeploySelectFormType;

use App\Service\FileUploader;

class MicroserviceDeployController extends AbstractController {
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
    
    private $session;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_microservice_deploy_render",
    *   path = "/cp_microservice_deploy_render/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"GET", "POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_deploy.html.twig")
    */
    public function renderAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->session = $this->utility->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        $microserviceDeployRows = $this->query->selectAllMicroserviceDeployDatabase();

        $form = $this->createForm(MicroserviceDeploySelectFormType::class, null, Array(
            'validation_groups' => Array('microservice_deploy_render'),
            'choicesId' => array_column($microserviceDeployRows, "id", "name")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $microserviceDeployRow = $this->query->selectMicroserviceDeployDatabase($form->get("id")->getData());
                
                $this->response['values']['renderHtml'] = $this->createRenderHtml($microserviceDeployRow);
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_1");
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
    *   name = "cp_microservice_deploy_create",
    *   path = "/cp_microservice_deploy_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_deploy_create.html.twig")
    */
    public function createAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->session = $this->utility->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        $microserviceDeployEntity = new MicroserviceDeploy();
        
        $this->session->set("microserviceDeployProfileId", 0);
        
        $form = $this->createForm(MicroserviceDeployFormType::class, $microserviceDeployEntity, Array(
            'validation_groups' => Array('microservice_deploy_create')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $microserviceDeployEntity = $this->fileUpload($form, $microserviceDeployEntity);
                
                $microserviceDeployEntity->setActive(true);
                
                $this->entityManager->persist($microserviceDeployEntity);
                $this->entityManager->flush();
                
                $this->microserviceDeployDatabase("update", $microserviceDeployEntity->getId(), $form->get("sshPassword")->getData(), $form->get("keyPrivatePassword")->getData(), $form->get("gitCloneUrlPassword")->getData());

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_2");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_3");
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
    *   name = "cp_microservice_deploy_select",
    *   path = "/cp_microservice_deploy_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_deploy_select.html.twig")
    */
    public function selectAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        $this->tableAndPagination = new TableAndPagination($this->utility);
        
        $this->session = $this->utility->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        $this->session->set("microserviceDeployProfileId", 0);
        
        $microserviceDeployRows = $this->query->selectAllMicroserviceDeployDatabase();
        
        $tableAndPagination = $this->tableAndPagination->request($microserviceDeployRows, 20, "microserviceDeploy", false);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(MicroserviceDeploySelectFormType::class, null, Array(
            'validation_groups' => Array('microservice_deploy_select'),
            'choicesId' => array_column($microserviceDeployRows, "id", "name")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
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
            'response' => $this->response,
            'form' => $form->createView()
        );
    }
    
    /**
    * @Route(
    *   name = "cp_microservice_deploy_profile",
    *   path = "/cp_microservice_deploy_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_deploy_profile.html.twig")
    */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->session = $this->utility->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $id = $request->get("id");
                
                $microserviceDeployEntity = $this->entityManager->getRepository("App\Entity\MicroserviceDeploy")->find($id);
                
                if ($microserviceDeployEntity != null) {
                    $this->session->set("microserviceDeployProfileId", $id);

                    $form = $this->createForm(MicroserviceDeployFormType::class, $microserviceDeployEntity, Array(
                        'validation_groups' => Array('microservice_deploy_profile')
                    ));
                    $form->handleRequest($request);
                    
                    $this->response['values']['id'] = $this->session->get("microserviceDeployProfileId");

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/microservice_deploy_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $form->createView()
                    ));
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_4");
            }
        }
        
        return $this->ajax->response(Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        ));
    }
    
    /**
    * @Route(
    *   name = "cp_microservice_deploy_profile_save",
    *   path = "/cp_microservice_deploy_profile_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_deploy_profile.html.twig")
    */
    public function profileSaveAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->session = $this->utility->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        $microserviceDeployEntity = $this->entityManager->getRepository("App\Entity\MicroserviceDeploy")->find($this->session->get("microserviceDeployProfileId"));
        $sshPasswordOld = $microserviceDeployEntity->getSshPassword();
        $keyPrivatePasswordOld = $microserviceDeployEntity->getKeyPrivatePassword();
        $gitCloneUrlPasswordOld = $microserviceDeployEntity->getGitCloneUrlPassword();
        
        $form = $this->createForm(MicroserviceDeployFormType::class, $microserviceDeployEntity, Array(
            'validation_groups' => Array('microservice_deploy_profile')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $microserviceDeployEntity = $this->fileUpload($form, $microserviceDeployEntity);
                
                $sshPassword = $sshPasswordOld;
                
                if ($form->get("sshUsername")->getData() == null || $form->get("sshUsername")->getData() == "")
                    $sshPassword = null;
                
                if ($form->get("sshPassword")->getData() == null || $form->get("sshPassword")->getData() == "") {
                    $microserviceDeployEntity->setSshPassword($sshPassword);
                    $sshPassword = "";
                }
                else
                    $sshPassword = $form->get("sshPassword")->getData();
                
                $keyPrivatePassword = $keyPrivatePasswordOld;
                
                if ($form->get("keyPrivate")->getData() == null || $form->get("keyPrivate")->getData() == "")
                    $keyPrivatePassword = null;
                
                if ($form->get("keyPrivatePassword")->getData() == null || $form->get("keyPrivatePassword")->getData() == "") {
                    $microserviceDeployEntity->setKeyPrivatePassword($keyPrivatePassword);
                    $keyPrivatePassword = "";
                }
                else
                    $keyPrivatePassword = $form->get("keyPrivatePassword")->getData();
                
                $gitCloneUrlPassword = $gitCloneUrlPasswordOld;
                
                if ($form->get("gitCloneUrlUsername")->getData() == null || $form->get("gitCloneUrlUsername")->getData() == "")
                    $gitCloneUrlPassword = null;
                
                if ($form->get("gitCloneUrlPassword")->getData() == null || $form->get("gitCloneUrlPassword")->getData() == "") {
                    $microserviceDeployEntity->setGitCloneUrlPassword($gitCloneUrlPassword);
                    $gitCloneUrlPassword = "";
                }
                else
                    $gitCloneUrlPassword = $form->get("gitCloneUrlPassword")->getData();
                
                $this->entityManager->persist($microserviceDeployEntity);
                $this->entityManager->flush();
                
                $this->microserviceDeployDatabase("update", $microserviceDeployEntity->getId(), $sshPassword, $keyPrivatePassword, $gitCloneUrlPassword);

                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_5");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_6");
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
    *   name = "cp_microservice_deploy_execute",
    *   path = "/cp_microservice_deploy_execute/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    */
    public function executeAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->session = $this->utility->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("action") == "clone" || $request->get("action") == "reset" || ($request->get("action") == "pull" && $request->get("branchName") != "")) {
                    $microserviceDeployRow = $this->query->selectMicroserviceDeployDatabase($request->get("id"));
                    
                    $sshConnection = $this->sshConnection($microserviceDeployRow, $request);
                    
                    if ($sshConnection == false)
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_18");
                    else {
                        $this->response['values']['sshConnection'] = $sshConnection;
                        
                        if ($sshConnection != "")
                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_7");
                        else
                            $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_8");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_9");
            }
        }
        
        return $this->ajax->response(Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        ));
    }
    
    /**
    * @Route(
    *   name = "cp_microservice_deploy_delete",
    *   path = "/cp_microservice_deploy_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_deploy_delete.html.twig")
    */
    public function deleteAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        $this->session = $this->utility->getSession();
        
        // Logic
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $this->session->get("microserviceDeployProfileId") : $request->get("id");

                    $microserviceDeployDatabase = $this->microserviceDeployDatabase("delete", $id);

                    if ($microserviceDeployDatabase == true) {
                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_10");
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $microserviceDeployDatabase = $this->microserviceDeployDatabase("deleteAll");

                    if ($microserviceDeployDatabase == true)
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceDeployController_11");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceDeployController_12");

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
    private function createRenderHtml($element) {
        $command = nl2br($element['command']);
        
        $renderHtml = "<ul class=\"mdc-list mdc-list--two-line mdc-list--avatar-list\">
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_1")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['name']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_2")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['description']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_3")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['system_user']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_4")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['ssh_username']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_5")}
                    <span class=\"mdc-list-item__secondary-text\">";
                        $renderHtml .= $element['ssh_password'] == null ? "" : "***";
                    $renderHtml .= "</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_6")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['key_public']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_8")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['key_private']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_10")}
                    <span class=\"mdc-list-item__secondary-text\">";
                        $renderHtml .= $element['key_private_password'] == null ? "" : "***";
                    $renderHtml .= "</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_11")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['ip']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_12")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['git_user_email']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_13")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['git_user_name']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_14")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['git_clone_url']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_15")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['git_clone_url_username']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_16")}
                    <span class=\"mdc-list-item__secondary-text\">";
                        $renderHtml .= $element['git_clone_url_password'] == null ? "" : "***";
                    $renderHtml .= "</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_17")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['git_clone_path']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_18")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['user_git_script']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_19")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['user_web_script']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_20")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['root_web_path']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->utility->getTranslator()->trans("microserviceDeployFormType_21")}
                    <span class=\"mdc-list-item__secondary-text\">{$command}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    <div style=\"margin-top: 6px;\" class=\"mdc-text-field mdc-text-field--outlined mdc-text-field--with-trailing-icon mdc-text-field--dense\">
                        <i class=\"material-icons mdc-text-field__icon\">textsms</i>
                        <input class=\"mdc-text-field__input\" type=\"text\" name=\"branchName\" value=\"\" required=\"required\" autocomplete=\"off\" aria-label=\"label\"/>
                        <label for=\"form_microservice_deploy_name\" class=\"mdc-floating-label\">{$this->utility->getTranslator()->trans("microserviceDeployFormType_26")}</label>
                        <div class=\"mdc-notched-outline\">
                            <svg>
                                <path class=\"mdc-notched-outline__path\"/>
                            </svg>
                        </div>
                        <div class=\"mdc-notched-outline__idle\"></div>
                    </div>
                    <span class=\"mdc-list-item__secondary-text\"></span>
                </span>
            </li>
        </ul>
        <button class=\"mdc-button mdc-button--dense mdc-button--raised git_execute\" data-action=\"clone\" type=\"submit\">{$this->utility->getTranslator()->trans("microserviceDeployController_13")}</button>
        <button class=\"mdc-button mdc-button--dense mdc-button--raised git_execute\" data-action=\"pull\" type=\"submit\">{$this->utility->getTranslator()->trans("microserviceDeployController_14")}</button>
        <button class=\"mdc-button mdc-button--dense mdc-button--raised git_execute\" data-action=\"reset\" type=\"submit\">{$this->utility->getTranslator()->trans("microserviceDeployController_15")}</button>";
        
        return $renderHtml;
    }
    
    private function createListHtml($elements) {
        $listHtml = "";
        
        foreach ($elements as $key => $value) {
            $listHtml .= "<tr>
                <td class=\"id_column\">
                    {$value['id']}
                </td>
                <td class=\"checkbox_column\">
                    <div class=\"mdc-checkbox\">
                        <input class=\"mdc-checkbox__native-control\" type=\"checkbox\"/>
                        <div class=\"mdc-checkbox__background\">
                            <svg class=\"mdc-checkbox__checkmark\" viewBox=\"0 0 24 24\">
                                <path class=\"mdc-checkbox__checkmark-path\" fill=\"none\" stroke=\"white\" d=\"M1.73,12.91 8.1,19.28 22.79,4.59\"/>
                            </svg>
                            <div class=\"mdc-checkbox__mixedmark\"></div>
                        </div>
                    </div>
                </td>
                <td>
                    {$value['name']}
                </td>
                <td>
                    {$value['description']}
                </td>
                <td>";
                    if ($value['active'] == 0)
                        $listHtml .= $this->utility->getTranslator()->trans("microserviceDeployController_16");
                    else
                        $listHtml .= $this->utility->getTranslator()->trans("microserviceDeployController_17");
                $listHtml .= "</td>
                <td class=\"horizontal_center\">
                    <button class=\"mdc-fab mdc-fab--mini cp_module_delete\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function microserviceDeployDatabase($type, $id = 0, $sshPassword = "", $keyPrivatePassword = "", $gitCloneUrlPassword = "") {
        if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM microservice_deploy
                                                                WHERE id = :id");
            
            $query->bindValue(":id", $id);

            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM microservice_deploy");

            return $query->execute();
        }
        
        if ($id > 0) {
            $settingRow = $this->query->selectSettingDatabase();
            
            if ($type == "select") {
                $query = $this->utility->getConnection()->prepare("SELECT AES_DECRYPT(:sshPassword, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512))) AS ssh_password,
                                                                        AES_DECRYPT(:keyPrivatePassword, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512))) AS key_private_password,
                                                                        AES_DECRYPT(:gitCloneUrlPassword, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512))) AS git_clone_url_password
                                                                        FROM microservice_deploy
                                                                    WHERE id = :id
                                                                    ORDER by name ASC");
                
                $query->bindValue(":sshPassword", $sshPassword);
                $query->bindValue(":keyPrivatePassword", $keyPrivatePassword);
                $query->bindValue(":gitCloneUrlPassword", $gitCloneUrlPassword);
                $query->bindValue(":id", $id);
                
                $query->execute();
                
                return $query->fetch();
            }
            else if ($type == "update") {
                if ($sshPassword != "") {
                    $query = $this->utility->getConnection()->prepare("UPDATE IGNORE microservice_deploy
                                                                        SET ssh_password = AES_ENCRYPT(:sshPassword, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512)))
                                                                        WHERE id = :id");

                    $query->bindValue(":sshPassword", $sshPassword);
                    $query->bindValue(":id", $id);

                    $query->execute();
                }

                if ($keyPrivatePassword != "") {
                    $query = $this->utility->getConnection()->prepare("UPDATE IGNORE microservice_deploy
                                                                        SET key_private_password = AES_ENCRYPT(:keyPrivatePassword, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512)))
                                                                        WHERE id = :id");

                    $query->bindValue(":keyPrivatePassword", $keyPrivatePassword);
                    $query->bindValue(":id", $id);

                    $query->execute();
                }
                
                if ($gitCloneUrlPassword != "") {
                    $query = $this->utility->getConnection()->prepare("UPDATE IGNORE microservice_deploy
                                                                        SET git_clone_url_password = AES_ENCRYPT(:gitCloneUrlPassword, UNHEX(SHA2('{$settingRow['secret_passphrase']}', 512)))
                                                                        WHERE id = :id");

                    $query->bindValue(":gitCloneUrlPassword", $gitCloneUrlPassword);
                    $query->bindValue(":id", $id);

                    $query->execute();
                }
                
                return true;
            }
        }
        
        return false;
    }
    
    private function fileUpload($form, $entity) {
        $row = $this->query->selectMicroserviceDeployDatabase($this->session->get("microserviceDeployProfileId"));
        
        $pathKeyPublic = $this->utility->getPathSrc() . "/files/microservice/deploy";
        $pathKeyPrivate = $this->utility->getPathSrc() . "/files/microservice/deploy";

        $keyPublic = $entity->getKeyPublic();
        $keyPrivate = $entity->getKeyPrivate();

        // Remove key public
        if (isset($row['key_public']) == true) {
            if ($form->get("removeKeyPublic")->getData() == true || ($keyPublic != null && $keyPublic != $row['key_public'])) {
                if ($row['key_public'] != "" && file_exists("$pathKeyPublic/{$row['key_public']}") == true)
                    unlink("$pathKeyPublic/{$row['key_public']}");

                $entity->setKeyPublic("");
            }
            else if ($row['key_public'] != "")
                $entity->setKeyPublic($row['key_public']);
        }

        // Upload key public
        if ($keyPublic != null && $form->get("removeKeyPublic")->getData() == false) {
            $fileName = $keyPublic->getClientOriginalName();
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newName = uniqid() . ".$extension";
            $keyPublic->move($pathKeyPublic, $newName);
            $entity->setKeyPublic($newName);
        }

        // Remove key private
        if (isset($row['key_private']) == true) {
            if ($form->get("removeKeyPrivate")->getData() == true || ($keyPrivate != null && $keyPrivate != $row['key_private'])) {
                if ($row['key_private'] != "" && file_exists("$pathKeyPrivate/{$row['key_private']}") == true)
                    unlink("$pathKeyPrivate/{$row['key_private']}");

                $entity->setKeyPrivate("");
                $entity->setKeyPrivatePassword("");
            }
            else if ($row['key_public'] != "")
                $entity->setKeyPrivate($row['key_private']);
        }

        // Upload key private
        if ($keyPrivate != null && $form->get("removeKeyPrivate")->getData() == false) {
            $fileName = $keyPrivate->getClientOriginalName();
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newName = uniqid() . ".$extension";
            $keyPrivate->move($pathKeyPrivate, $newName);
            $entity->setKeyPrivate($newName);
        }
        
        return $entity;
    }
    
    private function sshConnection($row, $request) {
        $result = "";
        
        if ($request->get("action") == "clone" || $request->get("action") == "pull" || $request->get("action") == "reset") {
            $pathKeyPublic = $this->utility->getPathSrc() . "/files/microservice/deploy/{$row['key_public']}";
            $pathKeyPrivate = $this->utility->getPathSrc() . "/files/microservice/deploy/{$row['key_private']}";
            
            $connection = @ssh2_connect($row['ip'], 22);
            
            if ($connection == false)
                return false;
            
            $auth = true;
            
            $sudo = "sudo";
            
            $microserviceDeployRow = $this->microserviceDeployDatabase("select", $row['id'], $row['ssh_password'], $row['key_private_password'], $row['git_clone_url_password']);
            
            if ($row['key_public'] == null || $row['key_private'] == null) {
                $auth = @ssh2_auth_password($connection, $row['ssh_username'], $microserviceDeployRow['ssh_password']);
                
                $sudo = "echo '{$microserviceDeployRow['ssh_password']}' | sudo -S";
            }
            else
                $auth = @ssh2_auth_pubkey_file($connection, $row['system_user'], $pathKeyPublic, $pathKeyPrivate, $row['key_private_password']);
            
            if ($auth == false)
                return false;
            
            if ($auth == true) {
                $commands = Array();
                
                if ($request->get("action") == "clone") {
                    $commands = Array(
                        "{$sudo} git config --global core.mergeoptions --no-edit",
                        "{$sudo} git config --global user.email '{$row['git_user_email']}'",
                        "{$sudo} git config --global user.name '{$row['git_user_name']}'",
                        "cd {$row['git_clone_path']}",
                        "{$sudo} -u {$row['user_git_script']} git clone https://{$row['git_clone_url_username']}:{$microserviceDeployRow['git_clone_url_password']}@{$row['git_clone_url']} {$row['git_clone_path']}",
                        "{$sudo} chown -R {$row['user_web_script']} {$row['root_web_path']}",
                        "{$sudo} find {$row['root_web_path']} -type d -exec chmod 775 {} \;",
                        "{$sudo} find {$row['root_web_path']} -type f -exec chmod 664 {} \;"
                    );
                }
                else if ($request->get("action") == "pull") {
                    $commands = Array(
                        "{$sudo} git config --global core.mergeoptions --no-edit",
                        "{$sudo} git config --global user.email '{$row['git_user_email']}'",
                        "{$sudo} git config --global user.name '{$row['git_user_name']}'",
                        "cd {$row['git_clone_path']}",
                        "{$sudo} -u {$row['user_git_script']} git pull --no-edit https://{$row['git_clone_url_username']}:{$microserviceDeployRow['git_clone_url_password']}@{$row['git_clone_url']} {$request->get("branchName")}",
                        "{$sudo} chown -R {$row['user_web_script']} {$row['root_web_path']}",
                        "{$sudo} find {$row['root_web_path']} -type d -exec chmod 775 {} \;",
                        "{$sudo} find {$row['root_web_path']} -type f -exec chmod 664 {} \;"
                    );
                }
                else if ($request->get("action") == "reset") {
                    $commands = Array(
                        "{$sudo} git config --global core.mergeoptions --no-edit",
                        "{$sudo} git config --global user.email '{$row['git_user_email']}'",
                        "{$sudo} git config --global user.name '{$row['git_user_name']}'",
                        "cd {$row['git_clone_path']}",
                        "{$sudo} -u {$row['user_git_script']} git fetch --all",
                        "{$sudo} -u {$row['user_git_script']} git reset --hard",
                        "{$sudo} chown -R {$row['user_web_script']} {$row['root_web_path']}",
                        "{$sudo} find {$row['root_web_path']} -type d -exec chmod 775 {} \;",
                        "{$sudo} find {$row['root_web_path']} -type f -exec chmod 664 {} \;"
                    );
                }
                
                $rowCommandSplit = preg_split("/\r\n|\r|\n/", $row['command']);
                
                foreach ($rowCommandSplit as $key => $value) {
                    $commands[] = $value;
                }
                
                $stream = ssh2_exec($connection, implode(";", $commands));
                
                stream_set_blocking($stream, true);
                
                $err_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
                $dio_stream = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
                
                stream_set_blocking($err_stream, true);
                stream_set_blocking($dio_stream, true);
                
                $result .= stream_get_contents($err_stream) . "\r\n";
                $result .= stream_get_contents($dio_stream) . "\r\n";
                
                fclose($stream);
            }
        }
        
        return "<pre>$result</pre>";
    }
}