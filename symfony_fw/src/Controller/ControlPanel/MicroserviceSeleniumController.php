<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;
use App\Classes\System\UploadChunk;
use App\Classes\System\TableAndPagination;

use App\Form\MicroserviceSeleniumSelectFormType;

class MicroserviceSeleniumController extends AbstractController {
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
    private $uploadChunk;
    
    private $session;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_microservice_selenium_select",
    *   path = "/cp_microservice_selenium_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"GET", "POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_selenium_select.html.twig")
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
        
        $this->session->set("microserviceSeleniumProfileId", 0);
        $this->session->set("microserviceSeleniumProfileName", "");
        
        $microserviceSeleniumFiles = $this->testFiles();
        
        $tableAndPagination = $this->tableAndPagination->request($microserviceSeleniumFiles, 20, "microservice_selenium", false);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(MicroserviceSeleniumSelectFormType::class, null, Array(
            'validation_groups' => Array("microservice_selenium_select"),
            'id' => array_column($microserviceSeleniumFiles, "id", "name")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            $id = 0;
            $name = "";
            
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $id = $request->get("id");
                $name = $request->get("name");
            }
            else if ($form->isSubmitted() == true && $form->isValid() == true) {
                $id = $form->get("id")->getData();
                $name = $form->get("name")->getData();
            }
            
            if ($request->get("event") != "refresh" && $request->get("event") != "tableAndPagination") {
                if (is_file("{$this->helper->getPathSrc()}/files/microservice/selenium/{$name}") == true) {
                    $this->session->set("microserviceUnitTestProfileId", $id);
                    $this->session->set("microserviceUnitTestProfileName", $name);

                    $this->response['values']['id'] = $this->session->get("microserviceUnitTestProfileId");
                    $this->response['values']['name'] = $this->session->get("microserviceUnitTestProfileName");

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/microservice_selenium_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response
                    ));
                }
                else {
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceSeleniumController_1");
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
    *   name = "cp_microservice_selenium_delete",
    *   path = "/cp_microservice_selenium_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_selenium_delete.html.twig")
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $path = "{$this->helper->getPathSrc()}/files/microservice/selenium";

                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $this->session->get("microserviceSeleniumProfileId") : $request->get("id");
                    $name = $request->get("name") == null ? $this->session->get("microserviceSeleniumProfileName") : $request->get("name");
                    
                    if (file_exists("{$path}/{$name}") == true)
                        unlink("{$path}/{$name}");
                    
                    $this->response['values']['id'] = $id;
                    
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceSeleniumController_2");
                }
                else if ($request->get("event") == "deleteAll") {
                    $this->helper->removeDirRecursive($path, false);
                    
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceSeleniumController_3");
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceSeleniumController_4");

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
    *   name = "cp_microservice_selenium_test",
    *   path = "/cp_microservice_selenium_test/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_selenium_profile.html.twig")
    */
    public function testAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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

        $settingRow = $this->helper->getSettingRow();
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());

        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $createProcessLock = $this->helper->createProcessLock("selenium");
                
                if ($createProcessLock == true) {
                    $path = "{$this->helper->getPathSrc()}/files/microservice/selenium";
                    $name = $request->get("name");

                    if (is_file("{$path}/{$name}") == true) {
                        $screen = "1920x1080";
                        $browserName = "";
                        $browserOption = "";

                        if ($request->get("event") == "chrome") {
                            $browserName = "Chrome";
                            $browserOption = "browserName=chrome goog:chromeOptions.args=[--headless,--nogpu,--window-size={$screen}]";
                        }
                        else if ($request->get("event") == "firefox") {
                            $browserName = "Firefox";
                            $browserOption = "browserName=firefox moz:firefoxOptions.args=[--headless,--nogpu,--window-size={$screen}]";
                        }

                        if ($settingRow['server_key_public'] == null && $settingRow['server_key_private'] == null) {
                            $sshConnection = $this->helper->sshConnection(
                                $settingRow['server_ip'],
                                22,
                                $settingRow['server_ssh_username'],
                                Array(
                                    $settingRow['server_ssh_password_decrypt']
                                )
                            );
                        }
                        else {
                            $pathKeyPublic = "{$this->helper->getPathSrc()}/files/setting/{$settingRow['server_key_public']}";
                            $pathKeyPrivate = "{$this->helper->getPathSrc()}/files/setting/{$settingRow['server_key_private']}";

                            $sshConnection =  $this->helper->sshConnection(
                                $settingRow['server_ip'],
                                22,
                                $settingRow['server_ssh_username'],
                                Array(
                                    $pathKeyPublic,
                                    $pathKeyPrivate,
                                    $settingRow['server_key_private_password_decrypt']
                                )
                            );
                        }

                        if ($sshConnection == true) {
                            $commands = Array("sudo -u {$settingRow['server_user']} selenium-side-runner -c \"{$browserOption}\" \"{$path}/{$name}\"");

                            $sshExecution = $this->helper->sshExecution($commands);

                            $this->response['result'] = "{$browserName}\r\n\r\n{$sshExecution}";

                            $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceSeleniumController_5");
                        }
                        else
                            $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceSeleniumController_6");

                        $this->helper->removeProcessLock();
                    }
                    else
                        $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceSeleniumController_6");
                }
                else
                    $this->response = $this->helper->responseProcessLock($this->response);
                
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
    *   name = "cp_microservice_selenium_upload",
    *   path = "/cp_microservice_selenium_upload/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_selenium_profile.html.twig")
    */
    public function uploadAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->helper = new Helper($this->container, $this->entityManager, $translator);
        $this->query = $this->helper->getQuery();
        $this->ajax = new Ajax($this->helper);
        $this->uploadChunk = new UploadChunk($this->helper);
        
        $this->session = $this->helper->getSession();
        
        // Logic
        $this->urlLocale = $this->session->get("languageTextCode") == null ? $_locale : $this->session->get("languageTextCode");
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "upload") {
                    $this->uploadChunk->setSettings(Array(
                        'path' => "{$this->helper->getPathSrc()}/files/microservice/selenium",
                        'chunkSize' => 1048576,
                        'mimeType' => Array("text/plain"),
                        'replace' => true
                    ));
                    $uploadChunkProcessFile = $this->uploadChunk->processFile();
                    
                    $this->response['uploadChunk']['processFile'] = $uploadChunkProcessFile;
                    
                    if (isset($uploadChunkProcessFile['status']) == true && $uploadChunkProcessFile['status'] == "complete")
                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceSeleniumController_7");
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceSeleniumController_8");
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
    private function testFiles() {
        $result = Array();

        $filePath = "{$this->helper->getPathSrc()}/files/microservice/selenium";

        $scanDirElements = preg_grep("/^([^.])/", scandir($filePath));

        $resultCount = 0;

        if ($scanDirElements != false) {
            foreach ($scanDirElements as $key => $value) {
                $extension = pathinfo($value, PATHINFO_EXTENSION);

                if ($value != "." && $value != ".." && $extension == "side") {
                    $result[$resultCount]['id'] = $resultCount + 1;
                    $result[$resultCount]['name'] = $value;

                    $resultCount ++;
                }
            }
        }

        return $result;
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
                <td class=\"name_column\">
                    {$value['name']}
                </td>
                <td class=\"horizontal_center\">
                    <button class=\"mdc-fab mdc-fab--mini cp_microservice_selenium_delete icon_warning\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
}