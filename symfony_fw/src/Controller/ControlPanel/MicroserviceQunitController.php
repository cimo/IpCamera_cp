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

use App\Entity\MicroserviceQunit;
use App\Form\MicroserviceQunitFormType;
use App\Form\MicroserviceQunitSelectFormType;

class MicroserviceQunitController extends AbstractController {
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
    *   name = "cp_microservice_qunit_create",
    *   path = "/cp_microservice_qunit_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_qunit_create.html.twig")
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
        
        $microserviceQunitEntity = new MicroserviceQunit();
        
        $this->session->set("microserviceQunitProfileId", 0);
        
        $form = $this->createForm(MicroserviceQunitFormType::class, $microserviceQunitEntity, Array(
            'validation_groups' => Array("microservice_qunit_create")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($microserviceQunitEntity);
                $this->entityManager->flush();
                
                $this->createFile($microserviceQunitEntity);
                
                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceQunitController_1");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceQunitController_2");
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
    *   name = "cp_microservice_qunit_select",
    *   path = "/cp_microservice_qunit_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_qunit_select.html.twig")
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
        
        $this->session->set("microserviceQunitProfileId", 0);
        
        $microserviceQunitRows = $this->query->selectAllMicroserviceQunitDatabase();
        
        $tableAndPagination = $this->tableAndPagination->request($microserviceQunitRows, 20, "microservice_qunit", false);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(MicroserviceQunitSelectFormType::class, null, Array(
            'validation_groups' => Array("microservice_qunit_select"),
            'id' => array_column($microserviceQunitRows, "id", "name")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            $id = 0;
            
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true)
                $id = $request->get("id");
            else if ($form->isSubmitted() == true && $form->isValid() == true)
                $id = $form->get("id")->getData();
            
            if ($request->get("event") != "refresh" && $request->get("event") != "tableAndPagination") {
                $microserviceQunitEntity = $this->entityManager->getRepository("App\Entity\MicroserviceQunit")->find($id);

                if ($microserviceQunitEntity != null) {
                    $this->session->set("microserviceQunitProfileId", $microserviceQunitEntity->getId());

                    $formSub = $this->createForm(MicroserviceQunitFormType::class, $microserviceQunitEntity, Array(
                        'validation_groups' => Array("microservice_qunit_profile")
                    ));
                    $formSub->handleRequest($request);

                    $this->response['values']['id'] = $this->session->get("microserviceQunitProfileId");

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/microservice_qunit_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $formSub->createView()
                    ));
                }
                else {
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceQunitController_3");
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
    *   name = "cp_microservice_qunit_profile",
    *   path = "/cp_microservice_qunit_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_qunit_profile.html.twig")
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
        
        $microserviceQunitEntity = $this->entityManager->getRepository("App\Entity\MicroserviceQunit")->find($this->session->get("microserviceQunitProfileId"));
        
        $form = $this->createForm(MicroserviceQunitFormType::class, $microserviceQunitEntity, Array(
            'validation_groups' => Array("microservice_qunit_profile")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($microserviceQunitEntity);
                $this->entityManager->flush();
                
                $this->createFile($microserviceQunitEntity);
                
                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceQunitController_4");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceQunitController_5");
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
    *   name = "cp_microservice_qunit_delete",
    *   path = "/cp_microservice_qunit_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_qunit_delete.html.twig")
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
                $path = "{$this->helper->getPathPublic()}/files/microservice/qunit/run";

                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $this->session->get("microserviceQunitProfileId") : $request->get("id");
                    
                    $microserviceQunitRow = $this->query->selectMicroserviceQunitDatabase($id);

                    $microserviceQunitDatabase = $this->query->deleteMicroserviceQunitDatabase("one", $id);
                    
                    if ($microserviceQunitDatabase == true) {
                        if (file_exists("{$path}/{$microserviceQunitRow['name']}.html") == true)
                            unlink("{$path}/{$microserviceQunitRow['name']}.html");
                        
                        $this->response['values']['id'] = $id;
                        
                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceQunitController_6");
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $microserviceQunitDatabase = $this->query->deleteMicroserviceQunitDatabase("all");

                    if ($microserviceQunitDatabase == true) {
                        $this->helper->removeDirRecursive($path, false);
                        
                        $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceQunitController_7");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceQunitController_8");

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
                <td>";
                    if ($value['active'] == 0)
                        $listHtml .= $this->helper->getTranslator()->trans("microserviceQunitController_9");
                    else
                        $listHtml .= $this->helper->getTranslator()->trans("microserviceQunitController_10");
                $listHtml .= "</td>
                <td class=\"horizontal_center\">
                    <button class=\"mdc-fab mdc-fab--mini cp_microservice_qunit_delete icon_warning\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function createFile($microserviceQunitEntity) {
        $settingRow = $this->helper->getSettingRow();
        
        $path = "{$this->helper->getPathPublic()}/files/microservice/qunit/run/{$microserviceQunitEntity->getName()}.html";
        
        $html = "<!DOCTYPE html>
        <html>
           <head>
                <title>Qunit</title>
                
                <!-- Meta -->
                <meta charset=\"UTF-8\"/>
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=5, user-scalable=1\">
                <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
                <meta name=\"description\" content=\"...\"/>
                <meta name=\"keywords\" content=\"...\"/>
                <meta name=\"robots\" content=\"index, follow\"/>
                
                <!-- Favicon -->
                <link href=\"{$this->helper->getUrlRoot()}/images/templates/{$settingRow['template']}/favicon.ico\" rel=\"icon\" type=\"image/x-icon\">
                
                <!-- Css -->
                <link href=\"{$this->helper->getUrlRoot()}/files/microservice/qunit/library/qunit_2.9.2.css\" rel=\"stylesheet\"/>
            </head>
            <body>";
                if ($microserviceQunitEntity->getActive() == true) {
                    $originReplace = trim(preg_replace("/\r\n|\r|\n/", ",", $microserviceQunitEntity->getOrigin()));
                    
                    $html .= "<div id=\"qunit\"></div>
                    <div id=\"qunit-fixture\"></div>
                    <div id=\"qunit_result\" style=\"display: none;\"></div>
                    <script src=\"{$this->helper->getUrlRoot()}/files/microservice/qunit/library/jquery_3.4.1.min.js\"></script>
                    <script src=\"{$this->helper->getUrlRoot()}/files/microservice/qunit/library/qunit_2.9.2.js\"></script>
                    <script>
                        \"use strict\";
                        
                        let qunitExecute = (event) => {
                            let result = $(\"#qunit_result\");
                            
                            let data = event.data;
                            let origin = event.origin;
                            let source = event.source;
                            
                            let originFilter = \"{$originReplace}\";
                            
                            let originFilterSplit = originFilter.split(\",\");
                            
                            if (originFilterSplit.includes(origin) === true) {
                                let json = JSON.parse(data);
                                
                                let page = json.page.replace(/<\/?(iframe|script)\b[^<>]*>/g, \"\");
                                
                                result.html(page);
                                
                                QUnit.test(\"{$microserviceQunitEntity->getName()}\", (assert) => {
                                    {$microserviceQunitEntity->getCode()}
                                });
                            }
                            else
                                result.html(\"\");
                        };
                        
                        window.addEventListener(\"message\", (event) => {
                            qunitExecute(event);
                        }, false);
                    </script>";
                }
                else
                    $html .= "<p>" . $this->helper->getTranslator()->trans("microserviceQunitController_11") . "</p>";
            $html .= "</body>
        </html>";
        
        file_put_contents($path, $html);
    }
}