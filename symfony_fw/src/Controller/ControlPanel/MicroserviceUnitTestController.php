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

use App\Entity\MicroserviceUnitTest;
use App\Form\MicroserviceUnitTestFormType;
use App\Form\MicroserviceUnitTestSelectFormType;

class MicroserviceUnitTestController extends AbstractController {
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
    *   name = "cp_microservice_unit_test_create",
    *   path = "/cp_microservice_unit_test_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_unit_test_create.html.twig")
    */
    public function createAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        $microserviceUnitTestEntity = new MicroserviceUnitTest();
        
        $_SESSION['microserviceUnitTestProfileId'] = 0;
        
        $form = $this->createForm(MicroserviceUnitTestFormType::class, $microserviceUnitTestEntity, Array(
            'validation_groups' => Array('microservice_unit_test_create')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($microserviceUnitTestEntity);
                $this->entityManager->flush();
                
                $this->createFile($microserviceUnitTestEntity);
                
                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceUnitTestController_1");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceUnitTestController_2");
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
    *   name = "cp_microservice_unit_test_select",
    *   path = "/cp_microservice_unit_test_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_unit_test_select.html.twig")
    */
    public function selectAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
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
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        $_SESSION['microserviceUnitTestProfileId'] = 0;
        
        $microserviceUnitTestRows = $this->query->selectAllMicroserviceUnitTestDatabase();
        
        $tableAndPagination = $this->tableAndPagination->request($microserviceUnitTestRows, 20, "microservice_unit_test", false);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(MicroserviceUnitTestSelectFormType::class, null, Array(
            'validation_groups' => Array('microservice_unit_test_select'),
            'choicesId' => array_column($microserviceUnitTestRows, "id", "name")
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
    *   name = "cp_microservice_unit_test_profile",
    *   path = "/cp_microservice_unit_test_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_unit_test_profile.html.twig")
    */
    public function profileAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                $id = $request->get("id");
                
                $microserviceUnitTestEntity = $this->entityManager->getRepository("App\Entity\MicroserviceUnitTest")->find($id);

                if ($microserviceUnitTestEntity != null) {
                    $_SESSION['microserviceUnitTestProfileId'] = $id;

                    $form = $this->createForm(MicroserviceUnitTestFormType::class, $microserviceUnitTestEntity, Array(
                        'validation_groups' => Array('microservice_unit_test_profile')
                    ));
                    $form->handleRequest($request);

                    $this->response['values']['id'] = $_SESSION['microserviceUnitTestProfileId'];

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/microservice_unit_test_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $form->createView()
                    ));
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceUnitTestController_3");
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
    *   name = "cp_microservice_unit_test_profile_save",
    *   path = "/cp_microservice_unit_test_profile_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_unit_test_profile.html.twig")
    */
    public function profileSaveAction($_locale, $urlCurrentPageId, $urlExtra, Request $request, TranslatorInterface $translator) {
        $this->urlLocale = isset($_SESSION['languageTextCode']) == true ? $_SESSION['languageTextCode'] : $_locale;
        $this->urlCurrentPageId = $urlCurrentPageId;
        $this->urlExtra = $urlExtra;
        
        $this->entityManager = $this->getDoctrine()->getManager();
        
        $this->response = Array();
        
        $this->utility = new Utility($this->container, $this->entityManager, $translator);
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax($this->utility);
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN", "ROLE_MICROSERVICE"), $this->getUser());
        
        $microserviceUnitTestEntity = $this->entityManager->getRepository("App\Entity\MicroserviceUnitTest")->find($_SESSION['microserviceUnitTestProfileId']);
        
        $form = $this->createForm(MicroserviceUnitTestFormType::class, $microserviceUnitTestEntity, Array(
            'validation_groups' => Array('microservice_unit_test_profile')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($microserviceUnitTestEntity);
                $this->entityManager->flush();
                
                $this->createFile($microserviceUnitTestEntity);
                
                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceUnitTestController_4");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceUnitTestController_5");
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
    *   name = "cp_microservice_unit_test_delete",
    *   path = "/cp_microservice_unit_test_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_unit_test_delete.html.twig")
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
        
        // Logic
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $_SESSION['microserviceUnitTestProfileId'] : $request->get("id");
                    
                    $microserviceUnitTestEntity = $this->entityManager->getRepository("App\Entity\MicroserviceUnitTest")->find($id);
                    
                    $microserviceUnitTestDatabase = $this->microserviceUnitTestDatabase("delete", $id);
                    
                    if ($microserviceUnitTestDatabase == true) {
                        unlink("{$this->utility->getPathPublic()}/files/microservice/unit_test/run/{$microserviceUnitTestEntity->getName()}.html");
                        
                        $this->response['values']['id'] = $id;
                        
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceUnitTestController_6");
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $microserviceUnitTestDatabase = $this->microserviceUnitTestDatabase("deleteAll");

                    if ($microserviceUnitTestDatabase == true) {
                        $this->utility->removeDirRecursive("{$this->utility->getPathPublic()}/files/microservice/unit_test/run", false);
                        
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceUnitTestController_7");
                    }
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceUnitTestController_8");

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
                        $listHtml .= $this->utility->getTranslator()->trans("microserviceUnitTestController_9");
                    else
                        $listHtml .= $this->utility->getTranslator()->trans("microserviceUnitTestController_10");
                $listHtml .= "</td>
                <td class=\"horizontal_center\">";
                    $listHtml .= "<button class=\"mdc-fab mdc-fab--mini cp_microservice_unit_test_delete\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function microserviceUnitTestDatabase($type, $id = null) {
        if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM microservice_unit_test
                                                                WHERE id = :id");
            
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM microservice_unit_test");
            
            return $query->execute();
        }
    }
    
    private function createFile($microserviceUnitTestEntity) {
        $path = "{$this->utility->getPathPublic()}/files/microservice/unit_test/run/{$microserviceUnitTestEntity->getName()}.html";
        
        $html = "<!DOCTYPE html>
        <html>
           <head>
                <title>Unit test</title>
                
                <!-- Meta -->
                <meta charset=\"UTF-8\"/>
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=5, user-scalable=1\">
                <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
                <!--<meta http-equiv=\"Content-Security-Policy\" content=\"upgrade-insecure-requests\">-->
                <meta name=\"description\" content=\"...\"/>
                <meta name=\"keywords\" content=\"...\"/>
                <meta name=\"robots\" content=\"index, follow\"/>
                
                <!-- Favicon -->
                <link href=\"favicon.ico\" rel=\"icon\" type=\"image/x-icon\">
                
                <!-- Css -->
                <link href=\"{$this->utility->getUrlRoot()}/files/microservice/unit_test/library/qunit_2.9.2.css\" rel=\"stylesheet\"/>
            </head>
            <body>";
                if ($microserviceUnitTestEntity->getActive() == true) {
                    $originReplace = trim(preg_replace("/\r\n|\r|\n/", ",", $microserviceUnitTestEntity->getOrigin()));
                    
                    $html .= "<div id=\"qunit\"></div>
                    <div id=\"qunit-fixture\"></div>
                    <div id=\"qunit_result\" style=\"display: none;\"></div>
                    <script src=\"{$this->utility->getUrlRoot()}/files/microservice/unit_test/library/jquery_3.4.1.min.js\"></script>
                    <script src=\"{$this->utility->getUrlRoot()}/files/microservice/unit_test/library/qunit_2.9.2.js\"></script>
                    <script>
                        var unitTestMessage = function(event) {
                            var data = event.data;
                            var origin = event.origin;
                            var source = event.source;
                            
                            var originFilter = \"{$originReplace}\";
                            
                            var originFilterSplit = originFilter.split(\",\");
                            
                            var result = $(\"#qunit_result\");
                            
                            if (originFilterSplit.includes(origin) === true) {
                                var json = JSON.parse(data);
                                
                                var page = $(json.page).not(\"#unitTest_script\").remove();
                                page = $(page).find(\"iframe\").remove().end();
                                
                                result.html(page);
                                
                                QUnit.test(\"{$microserviceUnitTestEntity->getName()}\", function(assert) {
                                    {$microserviceUnitTestEntity->getCode()}
                                });
                            }
                            else
                                result.html(\"\");
                        };
                        
                        window.addEventListener(\"message\", unitTestMessage, false);
                    </script>";
                }
                else
                    $html .= "<p>" . $this->utility->getTranslator()->trans("microserviceUnitTestController_11") . "</p>";
            $html .= "</body>
        </html>";
        
        file_put_contents($path, $html);
    }
}