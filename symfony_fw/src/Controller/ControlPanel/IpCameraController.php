<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Entity\IpCamera;
use App\Form\IpCameraFormType;
use App\Form\IpCameraSelectFormType;

class IpCameraController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $helper;
    private $query;
    private $queryCustom;
    private $ajax;
    private $tableAndPagination;
    
    private $session;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "ipCamera_render",
    *   path = "/ipCamera_render/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/page_action/ipCamera_render.html.twig")
    */
    public function renderAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->queryCustom = $this->helper->getQueryCustom();
        $this->ajax = new Ajax($this->helper);
        $this->tableAndPagination = new TableAndPagination($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR", "ROLE_IPCAMERA"), $this->getUser());
        
        if ($checkUserRole == true) {
            $ipCameraRows = $this->queryCustom->selectAllIpCameraDatabase();
            
            $devices = Array();
            
            foreach($ipCameraRows as $key => $value) {
                if ($value['user_id'] != null) {
                    $arrayFindValueExplode = $this->helper->arrayExplodeFindValue($value['user_id'], $this->getUser()->getId(), false);
                    
                    if ($arrayFindValueExplode == true)
                        $devices[] = $ipCameraRows[$key];
                }
            }
            
            foreach ($devices as $key => $value) {
                $ipCameraRow = $this->queryCustom->selectIpCameraDatabase("aes", $value['id'], $value['password']);
                
                if ($ipCameraRow != false) {
                    $url = "{$value['host_video']}&user={$value['username']}&pwd={$ipCameraRow['password']}";
                    
                    $this->response['values']['video'] = $this->createVideoHtml($key, $url);
                }
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
    *   name = "cp_ipCamera_create",
    *   path = "/cp_ipCamera_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/ipCamera_create.html.twig")
    */
    public function createAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->queryCustom = $this->helper->getQueryCustom();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR", "ROLE_IPCAMERA"), $this->getUser());
        
        $ipCameraEntity = new IpCamera();
        
        $this->session->set("ipCameraProfileId", 0);
        
        $form = $this->createForm(IpCameraFormType::class, $ipCameraEntity, Array(
            'validation_groups' => Array("ipCamera_create")
        ));
        $form->handleRequest($request);
        
        $this->response['values']['userSelectHtml'] = $this->helper->createUserSelectHtml("form_ipCamera_userId_select", "ipCameraController_1", true);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($ipCameraEntity);
                $this->entityManager->flush();
                
                $this->queryCustom->updateIpCameraDatabase("aes", $ipCameraEntity->getId(), "password", $form->get("password")->getData());
                
                mkdir("{$this->helper->getPathSrc()}/files/ipCamera/{$ipCameraEntity->getId()}");
                
                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("ipCameraController_2");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("ipCameraController_3");
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
    *   name = "cp_ipCamera_select",
    *   path = "/cp_ipCamera_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/ipCamera_select.html.twig")
    */
    public function selectAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->queryCustom = $this->helper->getQueryCustom();
        $this->ajax = new Ajax($this->helper);
        $this->tableAndPagination = new TableAndPagination($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR", "ROLE_IPCAMERA"), $this->getUser());
        
        $this->session->set("ipCameraProfileId", 0);
        
        $elements = $this->elementFilter();
        
        $tableAndPagination = $this->tableAndPagination->request($elements[0], 20, "ipCamera", false, true);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListDeviceHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(IpCameraSelectFormType::class, null, Array(
            'validation_groups' => Array("ipCamera_select"),
            'id' => array_column($elements[0], "id", "name")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            $id = 0;
            
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true)
                $id = $request->get("id");
            else if ($form->isSubmitted() == true && $form->isValid() == true)
                $id = $form->get("id")->getData();
            
            if ($request->get("event") != "refresh" && $request->get("event") != "tableAndPagination") {
                $ipCameraEntity = $this->entityManager->getRepository("App\Entity\IpCamera")->find($id);

                if ($ipCameraEntity != null) {
                    $this->session->set("ipCameraProfileId", $ipCameraEntity->getId());
                    
                    $formSub = $this->createForm(IpCameraFormType::class, $ipCameraEntity, Array(
                        'validation_groups' => Array('ipCamera_profile')
                    ));
                    $formSub->handleRequest($request);

                    $this->response['values']['userSelectHtml'] = $this->helper->createUserSelectHtml("form_ipCamera_userId_select", "ipCameraController_1", true);
                    $this->response['values']['id'] = $this->session->get("ipCameraProfileId");
                    
                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/ipCamera_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $formSub->createView()
                    ));
                }
                else {
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("ipCameraController_4");
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
    *   name = "cp_ipCamera_profile",
    *   path = "/cp_ipCamera_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/ipCamera_profile.html.twig")
    */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->queryCustom = $this->helper->getQueryCustom();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR", "ROLE_IPCAMERA"), $this->getUser());
        
        $ipCameraEntity = $this->entityManager->getRepository("App\Entity\IpCamera")->find($this->session->get("ipCameraProfileId"));
        
        $form = $this->createForm(IpCameraFormType::class, $ipCameraEntity, Array(
            'validation_groups' => Array("ipCamera_profile"),
            'password' => $ipCameraEntity->getPassword(),
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($ipCameraEntity);
                $this->entityManager->flush();
                
                $this->queryCustom->updateIpCameraDatabase("aes", $ipCameraEntity->getId(), "password", $form->get("password")->getData());
                
                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("ipCameraController_5");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("ipCameraController_6");
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
    *   name = "cp_ipCamera_delete",
    *   path = "/cp_ipCamera_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/ipCamera_delete.html.twig")
    */
    public function deleteAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->queryCustom = $this->helper->getQueryCustom();
        $this->ajax = new Ajax($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR", "ROLE_IPCAMERA"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $elements = $this->elementFilter();
                
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $this->session->get("ipCameraProfileId") : $request->get("id");
                    
                    foreach($elements[0] as $key => $value) {
                        if (isset($value['id']) == true && $value['id'] == $id) {
                            $ipCameraDatabase = $this->queryCustom->deleteIpCameraDatabase("one", $value['id']);
                            
                            if ($ipCameraDatabase == true) {
                                $this->deleteLogic("device", $id);

                                $this->response['values']['id'] = $id;

                                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("ipCameraController_7");
                            }
                        }
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    foreach($elements[0] as $key => $value) {
                        if (isset($value['id']) == true) {
                            $ipCameraDatabase = $this->queryCustom->deleteIpCameraDatabase("one", $value['id']);
                            
                            if ($ipCameraDatabase == true) {
                                $this->deleteLogic("device", $value['id']);
                                
                                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("ipCameraController_8");
                            }
                        }
                    }
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("ipCameraController_9");

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
    *   name = "cp_ipCamera_file",
    *   path = "/cp_ipCamera_file/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/ipCamera_file.html.twig")
    */
    public function fileAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->queryCustom = $this->helper->getQueryCustom();
        $this->ajax = new Ajax($this->helper);
        $this->tableAndPagination = new TableAndPagination($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR", "ROLE_IPCAMERA"), $this->getUser());
        
        $elements = $this->elementFilter();
        
        $tableAndPagination = $this->tableAndPagination->request($elements[1], 20, "ipCameraFile", false, false);

        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListFileHtml($tableAndPagination['listHtml'], $elements[0]);
        $this->response['values']['count'] = $tableAndPagination['count'];

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
            'response' => $this->response
        );
    }
    
    /**
    * @Route(
    *   name = "cp_ipCamera_file_download",
    *   path = "/cp_ipCamera_file_download/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    */
    public function fileDownloadAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->queryCustom = $this->helper->getQueryCustom();
        $this->ajax = new Ajax($this->helper);
        $this->tableAndPagination = new TableAndPagination($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR", "ROLE_IPCAMERA"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $elements = $this->elementFilter();
                
                foreach($elements[1] as $key => $value) {
                    $downloadPath = "{$this->helper->getPathSrc()}/files/ipCamera/{$key}/{$request->get("fileName")}";
                    
                    if (file_exists($downloadPath) == true) {
                        $downloadMime = mime_content_type($downloadPath);
                        
                        $this->helper->download($downloadPath, $downloadMime);
                    }
                }
            }
        }
        
        return new Response();
    }
    
    /**
    * @Route(
    *   name = "cp_ipCamera_file_delete",
    *   path = "/cp_ipCamera_file_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/ipCamera_file.html.twig")
    */
    public function fileDeleteAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->queryCustom = $this->helper->getQueryCustom();
        $this->ajax = new Ajax($this->helper);
        $this->tableAndPagination = new TableAndPagination($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MODERATOR", "ROLE_IPCAMERA"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $elements = $this->elementFilter();
                
                if ($request->get("event") == "delete") {
                    $findKey = $this->helper->arrayFindKeyWithValue($elements[0], "name", $request->get("deviceName"));
                    
                    $this->deleteLogic("file", $elements[0][$findKey]['id'], $request->get("fileName"));
                    
                    $this->response['values']['id'] = $request->get("id");
                    
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("ipCameraController_10");
                }
                else if ($request->get("event") == "deleteAll") {
                    foreach($elements[1] as $key => $value) {
                        foreach($value as $keySub => $valueSub) {
                            $this->deleteLogic("file", $key, $valueSub);
                        }
                    }
                    
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("ipCameraController_11");
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("ipCameraController_12");

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
    private function createVideoHtml($index, $url) {
        $html = "<i class=\"material-icons video_loading rotate_loop\">cached</i>
                <img id=\"video_{$index}\" class=\"video\" src=\"{$url}\" alt=\"video\"/>";
        
        return $html;
    }
    
    private function createListDeviceHtml($tableResult) {
        $listHtml = "";
        
        foreach ($tableResult as $key => $value) {
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
                <td>";
                    if ($value['active'] == 0)
                        $listHtml .= $this->helper->getTranslator()->trans("ipCameraController_13");
                    else
                        $listHtml .= $this->helper->getTranslator()->trans("ipCameraController_14");
                $listHtml .= "</td>
                <td class=\"horizontal_center\">
                    <button class=\"mdc-fab mdc-fab--mini cp_ipCamera_delete\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function createListFileHtml($tableResult, $devices) {
        $listHtml = "";
        
        foreach ($tableResult as $key => $value) {
            $id = $key + 1;
            
            foreach ($value as $keySub => $valueSub) {
                $fileSize = filesize("{$this->helper->getPathSrc()}/files/ipCamera/{$devices[$key]['id']}/{$valueSub}");
                
                $listHtml .= "<tr>
                    <td class=\"id_column\">
                        {$id}
                    </td>
                    <td class=\"deviceName_column\">
                        {$devices[$key]['name']}
                    </td>
                    <td>
                        <span class=\"cp_ipCamera_file_download cursor_custom\">
                            <i class=\"material-icons\">arrow_drop_down_circle</i>
                            <p>{$this->helper->unitFormat($fileSize)}</p>
                        </span>
                    </td>
                    <td class=\"fileName_column\">
                        {$valueSub}
                    </td>
                    <td class=\"horizontal_center\">
                        <button class=\"mdc-fab mdc-fab--mini cp_ipCamera_file_delete\" type=\"button\" aria-label=\"label\">
                            <span class=\"mdc-fab__icon material-icons\">delete</span>
                        </button>
                    </td>
                </tr>";
            }
        }
        
        return $listHtml;
    }
    
    private function elementFilter() {
        $ipCameraRows = $this->queryCustom->selectAllIpCameraDatabase();
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        $devices = Array();
        
        foreach($ipCameraRows as $key => $value) {
            if ($value['user_id'] != null) {
                if ($checkUserRole == true)
                    $devices[] = $ipCameraRows[$key];
                else {
                    $arrayFindValueExplode = $this->helper->arrayExplodeFindValue($value['user_id'], $this->getUser()->getId(), false);
                    
                    if ($arrayFindValueExplode == true)
                        $devices[] = $ipCameraRows[$key];
                }
            }
        }
        
        $files = Array();
        
        if (count($devices) > 0) {    
            foreach($devices as $key => $value) {
                $filePath = "{$this->helper->getPathSrc()}/files/ipCamera/{$value['id']}";
                
                $scanDirElements = preg_grep("/^([^.])/", scandir($filePath));
                
                if ($scanDirElements != false) {
                    foreach ($scanDirElements as $keySub => $valueSub) {
                        if ($valueSub != "." && $valueSub != "..")
                            $files[$value['id']][] = $valueSub;
                    }
                }
            }
        }
        
        return Array($devices, $files);
    }
    
    private function deleteLogic($type, $id = 0, $name = "") {
        if ($type == "device") {
            if ($id > 0) {
                $filePath = "{$this->helper->getPathSrc()}/files/ipCamera/$id";
                
                $this->helper->removeDirRecursive($filePath, true);
            }
        }
        else if ($type == "file") {
            if ($id > 0 && $name != "") {
                $filePath = "{$this->helper->getPathSrc()}/files/ipCamera/$id/$name";
                
                if (file_exists($filePath) == true)
                    unlink($filePath);
            }
        }
    }
}