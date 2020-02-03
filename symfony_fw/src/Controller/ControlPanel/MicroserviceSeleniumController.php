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
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        $this->session->set("microserviceSeleniumProfileId", 0);
        $this->session->set("microserviceSeleniumProfileName", "");
        
        $microserviceSeleniumFiles = $this->TestFiles();
        
        $tableAndPagination = $this->tableAndPagination->request($microserviceSeleniumFiles, 20, "microservice_selenium", false);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(MicroserviceSeleniumSelectFormType::class, null, Array(
            'validation_groups' => Array('microservice_selenium_select'),
            'choicesId' => array_column($microserviceSeleniumFiles, "id", "name")
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
    *   name = "cp_microservice_selenium_profile",
    *   path = "/cp_microservice_selenium_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_selenium_profile.html.twig")
    */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $id = $request->get("id");
                $name = $request->get("name");
                
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
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceSeleniumController_1");
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
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $this->session->get("microserviceSeleniumProfileId") : $request->get("id");
                    $name = $request->get("name") == null ? $this->session->get("microserviceSeleniumProfileName") : $request->get("name");
                    
                    unlink("{$this->helper->getPathSrc()}/files/microservice/selenium/{$name}");
                    
                    $this->response['values']['id'] = $id;
                    
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceSeleniumController_2");
                }
                else if ($request->get("event") == "deleteAll") {
                    $this->helper->removeDirRecursive("{$this->helper->getPathSrc()}/files/microservice/selenium", false);
                    
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
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        $settingRow = $this->helper->getSettingRow();
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $name = $request->get("name");
                $onlyName = preg_replace("/\\.[^.\\s]{3,4}$/", "", $name);
                
                $path = "{$this->helper->getPathSrc()}/files/microservice/selenium";
                $pathResult = "{$this->helper->getPathSrc()}/files/microservice/selenium/{$onlyName}.json";
                
                $screen = "1280x720";
                
                if (is_file("{$path}/{$name}") == true) {
                    $browserExecuted = "";
                    
                    if ($request->get("event") == "chrome") {
                        $browser = "browserName=chrome goog:chromeOptions.args=[--headless,--nogpu,--window-size={$screen}]";
                        
                        $browserExecuted = "Chrome";
                        
                        shell_exec("sudo bash {$path}/server_start.sh {$settingRow['server_user']} {$screen} {$path} {$_SERVER['SERVER_ADDR']} 4444 >/dev/null 2>&1 & echo $!");
                    }
                    else if ($request->get("event") == "firefox") {
                        $browser = "browserName=firefox moz:firefoxOptions.args=[--headless,--nogpu,--window-size={$screen}]";
                        
                        $browserExecuted = "Firefox";
                        
                        shell_exec("sudo bash {$path}/server_start.sh root {$screen} {$path} {$_SERVER['SERVER_ADDR']} 4444 >/dev/null 2>&1 & echo $!");
                    }
                    
                    sleep(3);
                    
                    $result = shell_exec("selenium-side-runner -s http://{$_SERVER['SERVER_ADDR']}:4444/wd/hub {$path}/{$name} -c {$browser} --output-directory={$path} 2>&1 & echo $?");
                    
                    if (is_file($pathResult) == true) {
                        $content = json_decode(file_get_contents($pathResult), true);
                        
                        $result = $this->readResult($content, $result);
                    }
                    
                    $seleniumList = shell_exec("ps aux | pgrep -f selenium_server 2>&1 & echo $!");
                    $seleniumListExplode = explode(PHP_EOL, $seleniumList);
                    
                    foreach ($seleniumListExplode as $key => $value) {
                        $pid = intval($value);
                        
                        if ($pid != 0)
                            shell_exec("sudo bash {$path}/server_stop.sh {$pid}");
                    }
                    
                    $xvfbList = shell_exec("ps aux | pgrep -f xvfb 2>&1 & echo $!");
                    $xvfbListExplode = explode(PHP_EOL, $xvfbList);
                    
                    foreach ($xvfbListExplode as $key => $value) {
                        $pid = intval($value);
                        
                        if ($pid != 0)
                            shell_exec("sudo bash {$path}/server_stop.sh {$pid}");
                    }
                    
                    if (is_file($pathResult) == true)
                        unlink($pathResult);
                    
                    $this->response['result'] = $browserExecuted . "\r\n" . $result;
                    
                    $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceSeleniumController_5");
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceSeleniumController_6");
                
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
        $sessionLanguageTextCode = $this->session->get("languageTextCode");
        
        $this->urlLocale = $sessionLanguageTextCode != null ? $sessionLanguageTextCode : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $checkUserRole = $this->helper->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "upload") {
                    $this->uploadChunk->setSettings(Array(
                        'path' => "{$this->helper->getPathSrc()}/files/microservice/selenium",
                        'chunkSize' => 1048576,
                        'mimeType' => Array("text/plain")
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
                    <button class=\"mdc-fab mdc-fab--mini cp_microservice_selenium_delete\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function readResult($content, &$result) {
        foreach ($content as $key => $value) {
            if (is_array($value) == true)
                $this->readResult($value, $result);
            else
                $result .= "{$key} => {$value}\n";
        }
        
        return $result;
    }
}