<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Entity\MicroserviceDeploy;
use App\Form\MicroserviceDeployFormType;
use App\Form\MicroserviceDeploySelectFormType;

class MicroserviceDeployController extends AbstractController {
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
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        $microserviceDeployRows = $this->query->selectAllMicroserviceDeployDatabase();

        $form = $this->createForm(MicroserviceDeploySelectFormType::class, null, Array(
            'validation_groups' => Array("microservice_deploy_render"),
            'id' => array_column($microserviceDeployRows, "id", "name")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $microserviceDeployRow = $this->query->selectMicroserviceDeployDatabase("normal", $form->get("id")->getData());
                
                $this->response['values']['renderHtml'] = $this->createRenderHtml($microserviceDeployRow);
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceDeployController_1");
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
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        $microserviceDeployEntity = new MicroserviceDeploy();
        
        $this->session->set("microserviceDeployProfileId", 0);
        
        $form = $this->createForm(MicroserviceDeployFormType::class, $microserviceDeployEntity, Array(
            'validation_groups' => Array("microservice_deploy_create")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->fileUpload($form, $microserviceDeployEntity);
                
                $this->entityManager->persist($microserviceDeployEntity);
                $this->entityManager->flush();
                
                $this->query->updateMicroserviceDeployDatabase("aes", $microserviceDeployEntity->getId(), "ssh_password", $form->get("sshPassword")->getData());
                $this->query->updateMicroserviceDeployDatabase("aes", $microserviceDeployEntity->getId(), "key_private_password", $form->get("keyPrivatePassword")->getData());
                $this->query->updateMicroserviceDeployDatabase("aes", $microserviceDeployEntity->getId(), "git_clone_url_password", $form->get("gitCloneUrlPassword")->getData());
                
                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceDeployController_2");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceDeployController_3");
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
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        $this->tableAndPagination = new TableAndPagination($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        $this->session->set("microserviceDeployProfileId", 0);
        
        $microserviceDeployRows = $this->query->selectAllMicroserviceDeployDatabase();
        
        $tableAndPagination = $this->tableAndPagination->request($microserviceDeployRows, 20, "microserviceDeploy", false);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(MicroserviceDeploySelectFormType::class, null, Array(
            'validation_groups' => Array("microservice_deploy_select"),
            'id' => array_column($microserviceDeployRows, "id", "name")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            $id = 0;
            
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true)
                $id = $request->get("id");
            else if ($form->isSubmitted() == true && $form->isValid() == true)
                $id = $form->get("id")->getData();
            
            if ($request->get("event") != "refresh" && $request->get("event") != "tableAndPagination") {
                $microserviceDeployEntity = $this->entityManager->getRepository("App\Entity\MicroserviceDeploy")->find($id);

                if ($microserviceDeployEntity != null) {
                    $this->session->set("microserviceDeployProfileId", $microserviceDeployEntity->getId());

                    $formSub = $this->createForm(MicroserviceDeployFormType::class, $microserviceDeployEntity, Array(
                        'validation_groups' => Array("microservice_deploy_profile")
                    ));
                    $formSub->handleRequest($request);

                    $this->response['values']['id'] = $this->session->get("microserviceDeployProfileId");
                    $this->response['values']['keyPublicLabel'] = $microserviceDeployEntity->getKeyPublic();
                    $this->response['values']['keyPrivateLabel'] = $microserviceDeployEntity->getKeyPrivate();

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/microservice_deploy_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $formSub->createView()
                    ));
                }
                else {
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceDeployController_4");
                    $this->response['errors'] = $this->ajax->errors($form);
                }
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
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        $microserviceDeployEntity = $this->entityManager->getRepository("App\Entity\MicroserviceDeploy")->find($this->session->get("microserviceDeployProfileId"));
        
        $form = $this->createForm(MicroserviceDeployFormType::class, $microserviceDeployEntity, Array(
            'validation_groups' => Array("microservice_deploy_profile"),
            'sshPassword' => $microserviceDeployEntity->getSshPassword(),
            'keyPrivatePassword' => $microserviceDeployEntity->getKeyPrivatePassword(),
            'gitCloneUrlPassword' => $microserviceDeployEntity->getGitCloneUrlPassword()
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->fileUpload($form, $microserviceDeployEntity);
                
                $this->entityManager->persist($microserviceDeployEntity);
                $this->entityManager->flush();
                
                $this->query->updateMicroserviceDeployDatabase("aes", $microserviceDeployEntity->getId(), "ssh_password", $form->get("sshPassword")->getData());
                $this->query->updateMicroserviceDeployDatabase("aes", $microserviceDeployEntity->getId(), "key_private_password", $form->get("keyPrivatePassword")->getData());
                $this->query->updateMicroserviceDeployDatabase("aes", $microserviceDeployEntity->getId(), "git_clone_url_password", $form->get("gitCloneUrlPassword")->getData());

                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceDeployController_5");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceDeployController_6");
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
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if (($request->get("action") == "pull" && $request->get("branchName") == "") || ($request->get("action") == "reset" && $request->get("branchName") == ""))
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceDeployController_9");
                else {
                    $id = $request->get("id") == null ? 0 : $request->get("id");
                    
                    $microserviceDeployRow = $this->query->selectMicroserviceDeployDatabase("normal", $id);
                    
                    $sshConnection = $this->sshConnection($microserviceDeployRow, $request);

                    if ($sshConnection == false)
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceDeployController_19");
                    else {
                        $this->response['values']['sshConnection'] = $sshConnection;
                        
                        if ($sshConnection != "")
                            $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceDeployController_7");
                        else
                            $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceDeployController_8");
                    }
                }
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
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $path = "{$this->helper->getPathSrc()}/files/microservice/deploy";

                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $this->session->get("microserviceDeployProfileId") : $request->get("id");

                    $microserviceDeployRow = $this->query->selectMicroserviceDeployDatabase($id);

                    $microserviceDeployDatabase = $this->query->deleteMicroserviceDeployDatabase("one", $id);

                    if ($microserviceDeployDatabase == true) {
                        if (file_exists("{$path}/{$microserviceDeployRow['key_public']}") == true)
                            unlink("{$path}/{$microserviceDeployRow['key_public']}");
                        
                        if (file_exists("{$path}/{$microserviceDeployRow['key_private']}") == true)
                            unlink("{$path}/{$microserviceDeployRow['key_private']}");

                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceDeployController_10");
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $microserviceDeployDatabase = $this->query->deleteMicroserviceDeployDatabase("all");

                    if ($microserviceDeployDatabase == true) {
                        $this->helper->removeDirRecursive($path, false);

                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceDeployController_11");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceDeployController_12");

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
    *   name = "cp_microservice_deploy_clearPassword",
    *   path = "/cp_microservice_deploy_clearPassword/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    */
    public function clearPasswordAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $reset = false;
                
                if ($request->get("inputName") == "form_microservice_deploy[sshPassword]") {
                    $reset = true;
                    
                    $this->query->updateMicroserviceDeployDatabase("clear", $this->session->get("microserviceDeployProfileId"), "ssh_password", null);
                }
                else if ($request->get("inputName") == "form_microservice_deploy[keyPrivatePassword]") {
                    $reset = true;
                    
                    $this->query->updateMicroserviceDeployDatabase("clear", $this->session->get("microserviceDeployProfileId"), "key_private_password", null);
                }
                
                if ($reset == true)
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceDeployController_20");
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceDeployController_21");
            }
        }
        
        return $this->ajax->response(Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        ));
    }
    
    // Functions private
    private function createRenderHtml($element) {
        $command = nl2br(base64_decode($element['command']));
        
        $renderHtml = "<ul class=\"mdc-list mdc-list--two-line mdc-list--avatar-list\">
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_1")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['name']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_2")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['description']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_3")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['system_user']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_4")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['ssh_username']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_5")}
                    <span class=\"mdc-list-item__secondary-text\">";
                        $renderHtml .= $element['ssh_password'] == null ? "" : "******";
                    $renderHtml .= "</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_6")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['key_public']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_8")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['key_private']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_10")}
                    <span class=\"mdc-list-item__secondary-text\">";
                        $renderHtml .= $element['key_private_password'] == null ? "" : "******";
                    $renderHtml .= "</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_11")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['ip']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_12")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['git_user_email']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_13")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['git_user_name']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_14")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['git_clone_url']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_15")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['git_clone_url_username']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_16")}
                    <span class=\"mdc-list-item__secondary-text\">";
                        $renderHtml .= $element['git_clone_url_password'] == null ? "" : "******";
                    $renderHtml .= "</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_17")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['git_clone_path']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_18")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['user_git_script']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_19")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['user_web_script']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_20")}
                    <span class=\"mdc-list-item__secondary-text\">{$element['root_web_path']}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item list_command\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    {$this->helper->getTranslator()->trans("microserviceDeployFormType_21")}
                    <span class=\"mdc-list-item__secondary-text\">{$command}</span>
                </span>
            </li>
            <li role=\"separator\" class=\"mdc-list-divider\"></li>
            <li class=\"mdc-list-item\">
                <span class=\"mdc-list-item__graphic material-icons\">info</span>
                <span class=\"mdc-list-item__text\">
                    <div class=\"form_row\">
                        <div class=\"mdc-text-field mdc-text-field--outlined mdc-text-field--with-trailing-icon mdc-text-field--dense\">
                        <input class=\"mdc-text-field__input\" type=\"text\" name=\"branchName\" value=\"\" autocomplete=\"off\" aria-label=\"label\">
                        <label class=\"mdc-floating-label\">{$this->helper->getTranslator()->trans("microserviceDeployFormType_26")}</label>
                        <div class=\"mdc-notched-outline\">
                            <svg>
                                <path class=\"mdc-notched-outline__path\"></path>
                            </svg>
                        </div>
                        <div class=\"mdc-notched-outline__idle\"></div>
                        <p class=\"mdc-text-field-helper-text\" aria-hidden=\"true\"></p>
                    </div>
                </span>
            </li>
        </ul>
        <button class=\"mdc-button mdc-button--dense mdc-button--raised git_execute\" data-action=\"clone\" type=\"submit\">{$this->helper->getTranslator()->trans("microserviceDeployController_13")}</button>
        <button class=\"mdc-button mdc-button--dense mdc-button--raised git_execute\" data-action=\"pull\" type=\"submit\">{$this->helper->getTranslator()->trans("microserviceDeployController_14")}</button>
        <button class=\"mdc-button mdc-button--dense mdc-button--raised git_execute\" data-action=\"fetch\" type=\"submit\">{$this->helper->getTranslator()->trans("microserviceDeployController_15")}</button>
        <button class=\"mdc-button mdc-button--dense mdc-button--raised git_execute\" data-action=\"reset\" type=\"submit\">{$this->helper->getTranslator()->trans("microserviceDeployController_16")}</button>";
        
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
                        $listHtml .= $this->helper->getTranslator()->trans("microserviceDeployController_17");
                    else
                        $listHtml .= $this->helper->getTranslator()->trans("microserviceDeployController_18");
                $listHtml .= "</td>
                <td class=\"horizontal_center\">
                    <button class=\"mdc-fab mdc-fab--mini cp_module_delete icon_warning\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function fileUpload($form, $entity) {
        $microserviceDeployRow = $this->query->selectMicroserviceDeployDatabase("normal", $this->session->get("microserviceDeployProfileId"));
        
        $pathKeyPublic = "{$this->helper->getPathSrc()}/files/microservice/deploy";
        $pathKeyPrivate = "{$this->helper->getPathSrc()}/files/microservice/deploy";

        $keyPublic = $entity->getKeyPublic();
        $keyPrivate = $entity->getKeyPrivate();

        // Remove key public
        if ($form->get("removeKeyPublic")->getData() == true || ($keyPublic != null && $keyPublic != $microserviceDeployRow['key_public'])) {
            if ($microserviceDeployRow['key_public'] != "" && file_exists("$pathKeyPublic/{$microserviceDeployRow['key_public']}") == true)
                unlink("$pathKeyPublic/{$microserviceDeployRow['key_public']}");
            
            $entity->setKeyPublic(null);
        }
        else if ($microserviceDeployRow['key_public'] != "")
            $entity->setKeyPublic($microserviceDeployRow['key_public']);
        
        // Upload key public
        if ($keyPublic != null && $form->get("removeKeyPublic")->getData() == false) {
            $fileName = $keyPublic->getClientOriginalName();
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $extension = $extension != "" ? ".{$extension}" : "";
            $newName = uniqid() . $extension;
            $keyPublic->move($pathKeyPublic, $newName);
            
            $entity->setKeyPublic($newName);
        }

        // Remove key private
        if ($form->get("removeKeyPrivate")->getData() == true || ($keyPrivate != null && $keyPrivate != $microserviceDeployRow['key_private'])) {
            if ($microserviceDeployRow['key_private'] != "" && file_exists("$pathKeyPrivate/{$microserviceDeployRow['key_private']}") == true)
                unlink("$pathKeyPrivate/{$microserviceDeployRow['key_private']}");
            
            $entity->setKeyPrivate(null);
            $entity->setKeyPrivatePassword(null);
        }
        else if ($microserviceDeployRow['key_public'] != "")
            $entity->setKeyPrivate($microserviceDeployRow['key_private']);
        
        // Upload key private
        if ($keyPrivate != null && $form->get("removeKeyPrivate")->getData() == false) {
            $fileName = $keyPrivate->getClientOriginalName();
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newName = uniqid() . ".$extension";
            $keyPrivate->move($pathKeyPrivate, $newName);
            
            $entity->setKeyPrivate($newName);
        }
    }
    
    private function sshConnection($row, $request) {
        $microserviceDeployRow = $this->query->selectMicroserviceDeployDatabase("aes", $row['id']);

        if ($row['key_public'] == null && $row['key_private'] == null) {
            $sshConnection = $this->helper->sshConnection(
                $row['ip'],
                22,
                $row['ssh_username'],
                Array(
                    $microserviceDeployRow['ssh_password_decrypt']
                )
            );
        }
        else {
            $pathKeyPublic = "{$this->helper->getPathSrc()}/files/microservice/deploy/{$row['key_public']}";
            $pathKeyPrivate = "{$this->helper->getPathSrc()}/files/microservice/deploy/{$row['key_private']}";

            $sshConnection =  $this->helper->sshConnection(
                $row['ip'],
                22,
                $row['ssh_username'],
                Array(
                    $pathKeyPublic,
                    $pathKeyPrivate,
                    $microserviceDeployRow['key_private_password_decrypt']
                )
            );
        }

        $commands = Array();
        $url = "https://{$row['git_clone_url_username']}:{$microserviceDeployRow['git_clone_url_password_decrypt']}@{$row['git_clone_url']}";
        $branchNameMatch = preg_match('/^[A-Za-z ]+$/', $request->get("branchName"));

        if ($request->get("action") == "clone") {
            $commands = Array(
                "sudo mkdir -p {$row['git_clone_path']}",
                "cd {$row['git_clone_path']}",
                "sudo git clone {$url} ."
            );
        }
        else if ($request->get("action") == "pull" && $branchNameMatch == true) {
            $commands = Array(
                "cd {$row['git_clone_path']}",
                "sudo git pull {$url} {$request->get("branchName")}"
            );
        }
        else if ($request->get("action") == "fetch") {
            $commands = Array(
                "cd {$row['git_clone_path']}",
                "sudo git fetch --all"
            );
        }
        else if ($request->get("action") == "reset" && $branchNameMatch == true) {
            $commands = Array(
                "cd {$row['git_clone_path']}",
                "sudo git reset --hard {$url} {$request->get("branchName")}"
            );
        }

        if ($sshConnection == false || count($commands) == 0)
            return false;

        $rowSplit = preg_split('/\r\n|\r|\n/', base64_decode($row['command']));

        foreach ($rowSplit as $key => $value) {
            $commands[] = $value;
        }

        $sshExecution = $this->helper->sshExecution($commands);
        
        return "<pre>$sshExecution</pre>";
    }
}