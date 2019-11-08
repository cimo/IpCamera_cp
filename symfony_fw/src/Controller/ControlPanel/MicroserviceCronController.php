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

use App\Entity\MicroserviceCron;
use App\Form\MicroserviceCronFormType;
use App\Form\MicroserviceCronSelectFormType;

class MicroserviceCronController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    
    private $session;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_microservice_cron_create",
    *   path = "/cp_microservice_cron_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_cron_create.html.twig")
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
        
        $microserviceCronEntity = new MicroserviceCron();
        
        $this->session->set("microserviceCronProfileId", 0);
        
        $form = $this->createForm(MicroserviceCronFormType::class, $microserviceCronEntity, Array(
            'validation_groups' => Array('microservice_cron_create')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($microserviceCronEntity);
                $this->entityManager->flush();
                
                $this->settingJob(false, $microserviceCronEntity);
                
                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceCronController_1");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceCronController_2");
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
    *   name = "cp_microservice_cron_select",
    *   path = "/cp_microservice_cron_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_cron_select.html.twig")
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
        
        $this->session->set("microserviceCronProfileId", 0);
        
        $microserviceCronRows = $this->query->selectAllMicroserviceCronDatabase();
        
        $tableAndPagination = $this->tableAndPagination->request($microserviceCronRows, 20, "microservice_cron", false);
        
        $this->response['values']['search'] = $tableAndPagination['search'];
        $this->response['values']['pagination'] = $tableAndPagination['pagination'];
        $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
        $this->response['values']['count'] = $tableAndPagination['count'];
        
        $form = $this->createForm(MicroserviceCronSelectFormType::class, null, Array(
            'validation_groups' => Array('microservice_cron_select'),
            'choicesId' => array_column($microserviceCronRows, "id", "name")
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
    *   name = "cp_microservice_cron_profile",
    *   path = "/cp_microservice_cron_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_cron_profile.html.twig")
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
                
                $microserviceCronEntity = $this->entityManager->getRepository("App\Entity\MicroserviceCron")->find($id);

                if ($microserviceCronEntity != null) {
                    $this->session->set("microserviceCronProfileId", $id);

                    $form = $this->createForm(MicroserviceCronFormType::class, $microserviceCronEntity, Array(
                        'validation_groups' => Array('microservice_cron_profile')
                    ));
                    $form->handleRequest($request);

                    $this->response['values']['id'] = $this->session->get("microserviceCronProfileId");
                    
                    $this->response['values']['lastExecution'] = $microserviceCronEntity->getLastExecution();

                    $this->response['render'] = $this->renderView("@templateRoot/render/control_panel/microservice_cron_profile.html.twig", Array(
                        'urlLocale' => $this->urlLocale,
                        'urlCurrentPageId' => $this->urlCurrentPageId,
                        'urlExtra' => $this->urlExtra,
                        'response' => $this->response,
                        'form' => $form->createView()
                    ));
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceCronController_3");
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
    *   name = "cp_microservice_cron_profile_save",
    *   path = "/cp_microservice_cron_profile_save/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_cron_profile.html.twig")
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
        
        $microserviceCronEntity = $this->entityManager->getRepository("App\Entity\MicroserviceCron")->find($this->session->get("microserviceCronProfileId"));
        
        $form = $this->createForm(MicroserviceCronFormType::class, $microserviceCronEntity, Array(
            'validation_groups' => Array('microservice_cron_profile')
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->entityManager->persist($microserviceCronEntity);
                $this->entityManager->flush();
                
                $this->settingJob(false, $microserviceCronEntity);
                
                $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceCronController_4");
            }
            else {
                $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceCronController_5");
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
    *   name = "cp_microservice_cron_delete",
    *   path = "/cp_microservice_cron_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_cron_delete.html.twig")
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
        
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_ADMIN"), $this->getUser());
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                if ($request->get("event") == "delete") {
                    $id = $request->get("id") == null ? $this->session->get("microserviceCronProfileId") : $request->get("id");
                    
                    $microserviceCronEntity = $this->entityManager->getRepository("App\Entity\MicroserviceCron")->find($id);
                    
                    $this->settingJob(true, $microserviceCronEntity);
                    
                    $microserviceCronDatabase = $this->microserviceCronDatabase("delete", $id);
                    
                    if ($microserviceCronDatabase == true) {
                        $this->response['values']['id'] = $id;
                        
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceCronController_6");
                    }
                }
                else if ($request->get("event") == "deleteAll") {
                    $microserviceCronDatabase = $this->microserviceCronDatabase("deleteAll");

                    if ($microserviceCronDatabase == true)
                        $this->response['messages']['success'] = $this->utility->getTranslator()->trans("microserviceCronController_7");
                }
                else
                    $this->response['messages']['error'] = $this->utility->getTranslator()->trans("microserviceCronController_8");

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
                        $listHtml .= $this->utility->getTranslator()->trans("microserviceCronController_9");
                    else
                        $listHtml .= $this->utility->getTranslator()->trans("microserviceCronController_10");
                $listHtml .= "</td>
                <td>
                    {$value['last_execution']}
                </td>
                <td class=\"horizontal_center\">";
                    $listHtml .= "<button class=\"mdc-fab mdc-fab--mini cp_microservice_cron_delete\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function microserviceCronDatabase($type, $id = null) {
        if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM microservice_cron
                                                                WHERE id = :id");
            
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("DELETE FROM microservice_cron");
            
            return $query->execute();
        }
    }
    
    private function settingJob($delete, $microserviceCronEntity) {
        $path = "{$this->utility->getPathSrc()}/files/microservice/cron";
        
        $code = $microserviceCronEntity->getCode() != "" ? " && {$microserviceCronEntity->getCode()}" : "";
        
        $command = "{$microserviceCronEntity->getTime()} (echo $(date){$code} && echo \"\" && php {$path}/system/CronJob.php {$microserviceCronEntity->getId()}) >> {$path}/{$microserviceCronEntity->getName()}.log";
        
        shell_exec("crontab -r");
        
        $this->utility->searchInFile("{$path}/system/cron_job.txt", "{$microserviceCronEntity->getName()}.log", " ");
        
        if ($delete == false) {
            if ($microserviceCronEntity->getActive() == true)
                shell_exec("grep -qxF '{$command}' {$path}/system/cron_job.txt || echo '{$command}' >> {$path}/system/cron_job.txt");
        }
        else
            unlink("{$path}/{$microserviceCronEntity->getName()}.log");
        
        shell_exec("crontab {$path}/system/cron_job.txt");
    }
}