<?php
namespace App\Controller\Microservice\Api\Basic;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;
use App\Classes\System\UploadChunk;
use App\Classes\System\ToolExcel;

use App\Entity\ApiBasic;
use App\Form\ApiBasicFormType;
use App\Form\ApiBasicSelectFormType;

class ApiBasicController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $helper;
    private $query;
    private $ajax;
    private $uploadChunk;
    private $toolExcel;
    
    private $session;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_apiBasic_create",
    *   path = "/cp_apiBasic_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/basic/create.html.twig")
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
        
        $apiBasicEntity = new ApiBasic();
        
        $form = $this->createForm(ApiBasicFormType::class, $apiBasicEntity, Array(
            'validation_groups' => Array("apiBasic_create")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($apiBasicEntity);
                $this->entityManager->flush();
                
                $this->query->updateApiBasicDatabase("aes", $apiBasicEntity->getId(), "database_password", $form->get("databasePassword")->getData());
                
                $this->response['values']['id'] = $apiBasicEntity->getId();
                
                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("apiBasicController_1");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_2");
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
    *   name = "cp_apiBasic_select",
    *   path = "/cp_apiBasic_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = "", "id" = "0"},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/basic/select.html.twig")
    */
    public function selectAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        $this->session->set("apiBasicProfileId", 0);
        
        $apiBasicRows = $this->query->selectAllApiBasicDatabase(true);
        
        $form = $this->createForm(ApiBasicSelectFormType::class, null, Array(
            'validation_groups' => Array("apiBasic_select"),
            'id' => array_column($apiBasicRows, "id", "name")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $id = $form->get("id")->getData() == null ? 0 : $form->get("id")->getData();
                
                $apiBasicEntity = $this->entityManager->getRepository("App\Entity\ApiBasic")->find($id);

                if ($apiBasicEntity != null) {
                    $this->session->set("apiBasicProfileId", $apiBasicEntity->getId());

                    $formSub = $this->createForm(ApiBasicFormType::class, $apiBasicEntity, Array(
                        'validation_groups' => Array("apiBasic_profile")
                    ));
                    $formSub->handleRequest($request);
                    
                    $this->response['render'] = $this->renderView("@templateRoot/microservice/api/basic/profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $formSub->createView()
                    ));
                }
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_3");
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
    *   name = "cp_apiBasic_profile",
    *   path = "/cp_apiBasic_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/basic/profile.html.twig")
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
        
        $apiBasicEntity = $this->entityManager->getRepository("App\Entity\ApiBasic")->find($this->session->get("apiBasicProfileId"));
        $nameOld = $apiBasicEntity->getName();
        
        $form = $this->createForm(ApiBasicFormType::class, $apiBasicEntity, Array(
            'validation_groups' => Array("apiBasic_profile"),
            'databasePassword' => $apiBasicEntity->getDatabasePassword()
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($apiBasicEntity);
                $this->entityManager->flush();
                
                $this->query->updateApiBasicDatabase("aes", $apiBasicEntity->getId(), "database_password", $form->get("databasePassword")->getData());
                
                $logPathOld = "{$this->helper->getPathSrc()}/files/microservice/api/basic/" . str_replace(" ", "_", $nameOld) . ".log";
                $logPathNew = "{$this->helper->getPathSrc()}/files/microservice/api/basic/" . str_replace(" ", "_", $apiBasicEntity->getName()) . ".log";
                
                if (file_exists($logPathOld) == true)
                    rename($logPathOld, $logPathNew);
                
                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("apiBasicController_4");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_5");
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
    *   name = "cp_apiBasic_delete",
    *   path = "/cp_apiBasic_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/microservice/api/basic/delete.html.twig")
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
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $this->session->get("apiBasicProfileId") : $request->get("id");
                    
                    $apiBasicEntity = $this->entityManager->getRepository("App\Entity\ApiBasic")->find($id);

                    if ($apiBasicEntity != null) {
                        $path = "{$this->helper->getPathSrc()}/files/microservice/api/basic";
                        $downloadPath = "{$this->helper->getPathPublic()}/files/microservice/api/basic";
                        
                        if (file_exists("{$path}/{$apiBasicEntity->getName()}.log") == true)
                            unlink("{$path}/{$apiBasicEntity->getName()}.log");
                        
                        if (file_exists("{$path}/{$apiBasicEntity->getName()}_csv.log") == true)
                            unlink("{$path}/{$apiBasicEntity->getName()}_csv.log");

                        $this->helper->removeDirRecursive($downloadPath, false);

                        $this->entityManager->remove($apiBasicEntity);
                        $this->entityManager->flush();
                        
                        $this->query->deleteApiBasicRequestDatabase($id);
                        
                        $this->response['values']['id'] = $id;

                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("apiBasicController_6");
                    }
                    else
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_7");
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_7");

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
    *   name = "cp_apiBasic_clearPassword",
    *   path = "/cp_apiBasic_clearPassword/{_locale}/{urlCurrentPageId}/{urlExtra}",
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
                if ($request->get("inputName") == "form_apiBasic[databasePassword]") {
                    $this->query->updateApiBasicDatabase("clear", $this->session->get("apiBasicProfileId"), "database_password", null);
                    
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("apiBasicController_17");
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_18");
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
    *   name = "cp_apiBasic_log",
    *   path = "/cp_apiBasic_log/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    */
    public function logAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
                if ($request->get("event") == "log") {
                    $apiBasicRow = $this->query->selectApiBasicDatabase($this->session->get("apiBasicProfileId"), false);
                    
                    $logPath = "{$this->helper->getPathSrc()}/files/microservice/api/basic/" . str_replace(" ", "_", $apiBasicRow['name']) . ".log";
                    
                    $fileReadTail = $this->helper->fileReadTail($logPath, "500");
                    
                    $this->response['values']['log'] = "<pre class=\"microservice_api_log\">" . implode("\r\n", $fileReadTail) . "</pre>";
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
    *   name = "cp_apiBasic_graph",
    *   path = "/cp_apiBasic_graph/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    */
    public function graphAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
                if ($request->get("event") == "graph") {
                    $this->response['render'] = $this->renderView("@templateRoot/include/chaato.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                    
                    $this->response['values']['selectPeriodYearHtml'] = $this->createSelectPeriodYearHtml($request);
                    $this->response['values']['selectPeriodMonthHtml'] = $this->createSelectPeriodMonthHtml($request);
                    
                    $labels = Array(
                        "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31"
                    );
                    
                    $testElements = $this->graphLogic($labels, "requestTestAction", "#ff0000");
                    
                    $this->response['values']['json'] = Array(
                        "label" => Array(
                            "name" => "Requests",
                            "items" => $labels
                        ),
                        "elements" => Array(
                            $testElements
                        )
                    );
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
    *   name = "cp_apiBasic_csv",
    *   path = "/cp_apiBasic_csv/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    */
    public function csvAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        $this->uploadChunk = new UploadChunk($this->helper);
        $this->toolExcel = new ToolExcel();
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "csv") {
                    $apiBasicRow = $this->query->selectApiBasicDatabase($this->session->get("apiBasicProfileId"), true);
                    
                    $createProcessLock = $this->helper->createProcessLock($apiBasicRow['name']);
                    
                    if ($createProcessLock == true) {
                        $path = "{$this->helper->getPathSrc()}/files/microservice/api/basic";
                        
                        $this->uploadChunk->setSettings(Array(
                            'path' => $path,
                            'chunkSize' => 1048576,
                            'mimeType' => Array("text/plain")
                        ));
                        $uploadChunkProcessFile = $this->uploadChunk->processFile();
                        
                        $this->response['uploadChunk']['processFile'] = $uploadChunkProcessFile;
                        
                        if (isset($uploadChunkProcessFile['status']) == true && $uploadChunkProcessFile['status'] == "complete") {
                            $this->helper->closeAjaxRequest($this->response, true);
                            
                            $readCsv = $this->toolExcel->readCsv("$path/{$uploadChunkProcessFile['fileName']}", ",", Array(), $this);
                            
                            if ($readCsv == true) {
                                //...
                            }
                            
                            $this->helper->removeProcessLock();
                        }
                    }
                    else
                        $this->response = $this->helper->responseProcessLock($this->response);
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
    
    public function readCsvCallback($index, $cell) {
        $apiBasicRow = $this->query->selectApiBasicDatabase($this->session->get("apiBasicProfileId"), true);

        if ($index == 0) {
            //$cell
        }
        else if ($index > 0) {
            $parameters = Array();
            
            //$cell
            
            $this->databaseExternal("test", $parameters, $apiBasicRow);
        }
        
        if (isset($this->response['errorCode']) == true && $this->response['errorCode'] != 0) {
            $name = "{$apiBasicRow['name']}_csv";
            
            $this->helper->writeLog("{$this->helper->getPathSrc()}/files/microservice/api/basic", $name, "readCsvCallback() =>", $this->response);
            
            $this->helper->removeProcessLock();
            
            return false;
        }
        
        return true;
    }
    
    /**
    * @Route(
    *   name = "cp_apiBasic_download_detail",
    *   path = "/cp_apiBasic_download_detail/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    */
    public function downloadDetailAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        $this->toolExcel = new ToolExcel();
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $downloadPath = "{$this->helper->getPathPublic()}/files/microservice/api/basic";
                
                if ($request->get("event") == "download_requestTestAction") {
                    $downloadName = rand() . "_point";
                    
                    $this->toolExcel->setPath($downloadPath);
                    $this->toolExcel->setName($downloadName);
                    
                    $this->toolExcel->createSheet("Point");
                    
                    $elements = Array(
                        'labels' => Array(
                            "name",
                            "errorCode"
                        )
                    );
                    
                    $apiBasicRequestDetailRows = $this->query->selectAllApiBasicRequestDetailDatabase("requestTestAction", $request->get("dateStart"), $request->get("dateEnd"));
                    
                    if ($apiBasicRequestDetailRows != false && count($apiBasicRequestDetailRows) > 0) {
                        foreach ($apiBasicRequestDetailRows as $key => $value) {
                            if (trim($value['data']) !== "false") {
                                $data = json_decode($value['data']);
                                
                                $name = isset($data->name) == false ? "" : $data->name;
                                $errorCode = isset($data->errorCode) == false ? 0 : intval($data->errorCode);
                                
                                $elements['items'] = Array(
                                    $name,
                                    $errorCode
                                );

                                $this->toolExcel->populateSheet($key, $elements);
                            }
                        }

                        $result = $this->toolExcel->save();
                        
                        if ($result == true && file_exists("{$downloadPath}/{$this->toolExcel->getName()}") == true) {
                            $url = "{$this->helper->getUrlRoot()}/files/microservice/api/basic";
                            
                            $this->response['values']['url'] = "{$url}/{$this->toolExcel->getName()}";
                        }
                        else
                            $this->response['messages']['error'] = $this->helper->getTranslator()->trans("download_1");
                    }
                    else
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("download_2");
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
    *   name = "apiBasic_requestCheck",
    *   path = "/apiBasic_requestCheck",
    *	methods={"POST", "OPTIONS"}
    * )
    */
    public function requestCheckAction(Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        
        // Logic
        if ($request->isMethod("POST") == true) {
            $parameters = Array();
            
            if (empty($request->getContent()) == false)
                $parameters = json_decode($request->getContent(), true);
            else
                $parameters = $request->request->all();
            
            $errorCode = $this->errorCode("requestControl", $parameters);
            
            if ($errorCode == false) {
                if (isset($parameters['event']) == true && $parameters['event'] == "requestCheck") {
                    $microserviceApiRow = $this->query->selectMicroserviceApiDatabase(1);
                    
                    if ($microserviceApiRow != false) {
                        $apiBasicRow = $this->query->selectApiBasicDatabase($parameters['tokenName'], true);
                        
                        if ($apiBasicRow != false) {
                            $ipSplit = preg_split('/\r\n|\r|\n/', $apiBasicRow['ip']);

                            if (isset($apiBasicRow['ip']) == true && in_array($_SERVER['REMOTE_ADDR'], $ipSplit) == false)
                                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_13");
                            else
                                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("apiBasicController_8");
                        }
                        else
                            $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_12");
                    }
                    else
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_9");
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_11");
            }
            else
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_11");
        }
        
        $response = new Response(json_encode($this->response));
        $response->headers->set("Access-Control-Allow-Origin", "*");
        $response->headers->set("Access-Control-Allow-Headers", "*");
        $response->headers->set("Access-Control-Allow-Methods", "POST, OPTIONS");
        $response->headers->set("Accept", "application/json");
        $response->headers->set("Content-Type", "application/json");
        
        return $response;
    }
    
    /**
    * @Route(
    *   name = "apiBasic_requestTest",
    *   path = "/apiBasic_requestTest",
    *	methods={"POST", "OPTIONS"}
    * )
    */
    public function requestTestAction(Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        
        // Logic
        $name = "requestTestAction";
        
        if ($request->isMethod("POST") == true) {
            $parameters = Array();

            if (empty($request->getContent()) == false)
                $parameters = json_decode($request->getContent(), true);
            else
                $parameters = $request->request->all();
            
            $errorCode = $this->errorCode("requestControl", $parameters);
            
            if ($errorCode == false) {
                if (isset($parameters['event']) == true && $parameters['event'] == "requestTest") {
                    $microserviceApiRow = $this->query->selectMicroserviceApiDatabase(1);
                    
                    $apiBasicRow = $this->query->selectApiBasicDatabase($parameters['tokenName'], true);
                    
                    $postFields = Array();
                    
                    if ($microserviceApiRow != false) {
                        if ($apiBasicRow != false) {
                            $ipSplit = preg_split('/\r\n|\r|\n/', $apiBasicRow['ip']);
                            
                            if (isset($apiBasicRow['ip']) == true && in_array($_SERVER['REMOTE_ADDR'], $ipSplit) == false)
                                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_13");
                            else {
                                if ($apiBasicRow['url_callback'] != "") {
                                    $urlCallback = $this->urlCallback($parameters, $apiBasicRow);
                                    
                                    if ($urlCallback == true)
                                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("apiBasicController_10");
                                }

                                $databaseExternal = $this->databaseExternal("test", $parameters, $apiBasicRow);
                                
                                if ($databaseExternal != false)
                                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("apiBasicController_10");
                            }
                        }
                        else
                            $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_12");
                    }
                    else
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_9");
                    
                    if ($apiBasicRow != false) {
                        $this->saveRequest($apiBasicRow, $name, "apiBasic -> {$name}");
                        
                        $this->saveRequestDetail($name, $postFields, $this->response['errorCode']);
                        
                        if ((isset($this->response['errorCode']) == true && $this->response['errorCode'] != 0) || isset($this->response['messages']['error']) == true)
                            $this->helper->writeLog("{$this->helper->getPathSrc()}/files/microservice/api/basic", $apiBasicRow['name'], "requestTestAction() =>", $this->response);
                    }
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_11");
            }
            else
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_11");
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
    private function graphLogic($labels, $tag, $color) {
        $apiBasicRequestRows = $this->query->selectAllApiBasicRequestDatabase($this->session->get("apiBasicProfileId"), $tag, $this->session->get("apiBasicGraphPeriod_year"), $this->session->get("apiBasicGraphPeriod_month"));
        
        $elementNames = array_fill(0, 31, "");
        $elementCounts = array_fill(0, 31, "");
        
        foreach ($labels as $key => $value) {
            $dateFormat = $this->helper->dateFormat("{$this->session->get("apiBasicGraphPeriod_year")}-{$this->session->get("apiBasicGraphPeriod_month")}-{$value}", false);
            
            foreach ($apiBasicRequestRows as $keySub => $valueSub) {
                $dateExplode = explode(" ", $valueSub['date']);
                
                $dateA = new \DateTime($dateFormat);
                $dateB = new \DateTime($dateExplode[0]);
                
                if (date_diff($dateA, $dateB)->y == 0 && date_diff($dateA, $dateB)->m == 0 && date_diff($dateA, $dateB)->d == 0) {
                    $elementNames[$key] = $valueSub['name'];
                    $elementCounts[$key] = intval($elementCounts[$key]) + intval($valueSub['count']);
                }
            }
        }
        
        return Array(
            "name" => $elementNames,
            "items" => $elementCounts,
            "color" => $color
        );
    }
    
    private function createSelectPeriodYearHtml($request) {
        $periodMin = new \DateTime("2019/01/01");
        $periodMax = new \DateTime(date("Y/01/01"));
        
        $difference = date_diff($periodMin, $periodMax)->y;
        
        $html = "<div style=\"width: 100px;\" class=\"mdc-select\">
            <select class=\"mdc-select__native-control graph_period_year\" name=\"graph_period_year\">";
            
            for ($a = 0; $a < $difference; $a ++) {
                $date = $periodMin->add(new \DateInterval("P1Y"));
                
                $year = date("Y");
                
                $sessionApiBasicGraphPeriodYear = $this->session->get("apiBasicGraphPeriod_year");

                if ($request->get("year") != null && $request->get("year") != "")
                    $year = $request->get("year");
                else if ($sessionApiBasicGraphPeriodYear != null)
                    $year = $sessionApiBasicGraphPeriodYear;
                
                $this->session->set("apiBasicGraphPeriod_year", $year);
                
                $periodMax = new \DateTime(date("$year/01/01"));
                
                $selected = "";
                
                if (date_diff($periodMin, $periodMax)->y == 0)
                    $selected = "selected=\"selected\"";
                
                $html .= "<option $selected value=\"{$date->format("Y")}\">{$date->format("Y")}</option>";
            }
            
            $html .= "</select>
            <label class=\"mdc-floating-label mdc-floating-label--float-above\">{$this->helper->getTranslator()->trans("apiBasicController_14")}</label>
            <div class=\"mdc-line-ripple\"></div>
        </div>";
        
        return $html;
    }
    
    private function createSelectPeriodMonthHtml($request) {
        $month = date("m");
        
        $sessionApiBasicGraphPeriodMonth = $this->session->get("apiBasicGraphPeriod_month");
        
        if ($request->get("month") != null && $request->get("month") != "")
            $month = $request->get("month");
        else if ($sessionApiBasicGraphPeriodMonth != null)
            $month = $sessionApiBasicGraphPeriodMonth;
        
        $this->session->set("apiBasicGraphPeriod_month", $month);
        
        $html = "<div style=\"width: 100px;\" class=\"mdc-select\">
            <select class=\"mdc-select__native-control graph_period_month\" name=\"graph_period_month\">";
            
            for ($a = 1; $a <= 12; $a ++) {
                $aTmp = sprintf("%02d", $a);
                
                $selected = "";
                
                if ($aTmp == $month)
                    $selected = "selected=\"selected\"";
                
                $html .= "<option $selected value=\"$aTmp\">$aTmp</option>";
            }
            
            $html .= "</select>
            <label class=\"mdc-floating-label mdc-floating-label--float-above\">{$this->helper->getTranslator()->trans("apiBasicController_15")}</label>
            <div class=\"mdc-line-ripple\"></div>
        </div>";
        
        return $html;
    }
    
    private function errorCode($type, $parameters) {
        $errorCode = 0;
        
        if ($type == "requestControl") {
            if (isset($parameters['event']) == false || $parameters['event'] == "")
                $errorCode = 1;
            if (isset($parameters['tokenName']) == false || $parameters['tokenName'] == "")
                $errorCode = 2;
        }
        
        $this->response['errorCode'] = $errorCode;
        
        return $errorCode;
    }
    
    private function urlCallback($parameters, $row) {
        $curl = curl_init();
        
        if ($curl == false)
            $this->response['messages']['error'] = $this->helper->getTranslator()->trans("apiBasicController_16");
        else {
            $postFields = Array(
                'event' => 'apiBasic'
            );

            curl_setopt($curl, CURLOPT_URL, $row['url_callback']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_ENCODING, "");
            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
            curl_setopt($curl, CURLOPT_TIMEOUT, 120);
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));
            curl_setopt($curl, CURLOPT_HTTPHEADER, Array(
                "Content-Type: application/json"
            ));

            $curlResponse = curl_exec($curl);
            $curlError = curl_error($curl);
            $curlInfo = curl_getinfo($curl);
            
            curl_close($curl);
            
            if ($curlInfo['http_code'] == "200") {
                $this->response['messages']['urlCallbackResponse'] = $curlResponse;
                $this->response['messages']['urlCallbackError'] = $curlError;
                $this->response['messages']['urlCallbackInfo'] = $curlInfo;

                return true;
            }
        }
        
        return false;
    }
    
    private function databaseExternal($type, $parameters, $row) {
        $response = false;
        
        if ($row['database_ip'] != "" && $row['database_name'] != "" && $row['database_username'] != "" && $row['database_password_decrypt'] != "") {
            $checkHost = $this->helper->checkHost($row['database_ip']);
            
            if ($checkHost != false) {
                try {
                    $pdo = new \PDO("mysql:host={$row['database_ip']};dbname={$row['database_name']};charset=utf8", $row['database_username'], $row['database_password_decrypt']);
                }
                catch(\PDOException $error) {
                    $pdo = false;

                    $this->response['messages']['error'] = $error;
                }

                if ($pdo != false) {
                    if ($type == "test") {
                        //...

                        $response = true;
                    }
                }
                
                unset($pdo);
            }
            
            if ($response == false) {
                $this->response['errorCode'] = 10;
                
                $this->helper->writeLog("{$this->helper->getPathSrc()}/files/microservice/api/basic", $row['name'], "databaseExternal() =>", $this->response);
            }
        }
        
        return $response;
    }
    
    private function saveRequest($row, $name, $message) {
        $apiBasicRequestRow = $this->query->selectApiBasicRequestDatabase($row['id'], $name);
        
        if ($apiBasicRequestRow == false)
            $this->query->insertApiBasicRequestDatabase($row['id'], $name);
        else
            $this->query->updateApiBasicRequestDatabase($row['id'], $name, $apiBasicRequestRow['count']);
        
        if ($row['slack_active'] == true)
            $this->helper->sendMessageToSlackRoom("api_basic", "{$this->helper->dateFormat()} - IP[{$_SERVER['REMOTE_ADDR']}] - Message: {$message}");
        
        if ($row['line_active'] == true)
            $this->helper->sendMessageToLineChatMultiple("api_basic", "{$this->helper->dateFormat()} - IP[{$_SERVER['REMOTE_ADDR']}] - Message: {$message}");
    }
    
    private function saveRequestDetail($name, $data, $errorCode) {
        $data['errorCode'] = $errorCode;
        
        $this->query->insertApiBasicRequestDetailDatabase($name, json_encode($data));
    }
}