<?php
namespace App\Controller\ControlPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Helper;
use App\Classes\System\Ajax;

use App\Entity\MicroserviceApi;
use App\Form\MicroserviceApiFormType;

class MicroserviceApiController extends AbstractController {
    // Vars
    private $urlLocale;
    private $urlCurrentPageId;
    private $urlExtra;
    
    private $entityManager;
    
    private $response;
    
    private $helper;
    private $query;
    private $ajax;
    
    private $session;
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "cp_microservice_api_render",
    *   path = "/cp_microservice_api_render/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"GET"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_api.html.twig")
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
        
        if ($request->isMethod("GET") == true && $checkUserRole == true)
            $this->response['values']['microserviceApiRows'] = $this->query->selectAllMicroserviceApiDatabase(true);
        
        return Array(
            'urlLocale' => $this->urlLocale,
            'urlCurrentPageId' => $this->urlCurrentPageId,
            'urlExtra' => $this->urlExtra,
            'response' => $this->response
        );
    }
    
    /**
    * @Route(
    *   name = "cp_microservice_api_create",
    *   path = "/cp_microservice_api_create/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_api_create.html.twig")
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
        
        $microserviceApiEntity = new MicroserviceApi();
        
        $this->session->set("microserviceApiProfileId", 0);
        
        $form = $this->createForm(MicroserviceApiFormType::class, $microserviceApiEntity, Array(
            'validation_groups' => Array("microservice_api_create")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->fileUpload($form, $microserviceApiEntity);
                
                $this->entityManager->persist($microserviceApiEntity);
                $this->entityManager->flush();

                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceApiController_1");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceApiController_2");
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
    *   name = "cp_microservice_api_profile",
    *   path = "/cp_microservice_api_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = "", "id" = "0"},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"GET", "POST"}
    * )
    * @Template("@templateRoot/render/control_panel/microservice_api_profile.html.twig")
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
        
        $settingRow = $this->helper->getSettingRow();
        
        $urlExtraExplode = explode("_", $this->urlExtra);
        
        $id = isset($urlExtraExplode[4]) == true ? $urlExtraExplode[4] : 0;
        
        $microserviceApiEntity = $this->entityManager->getRepository("App\Entity\MicroserviceApi")->find($id);
        
        if ($microserviceApiEntity != null) {
            $this->session->set("microserviceApiProfileId", $microserviceApiEntity->getId());
            
            $logo = "{$this->helper->getUrlRoot()}/images/templates/{$settingRow['template']}/api.png";
            
            if ($microserviceApiEntity->getImage() != "" && file_exists("{$this->helper->getPathPublic()}/files/microservice/api/{$microserviceApiEntity->getImage()}") == true)
                $logo = "{$this->helper->getUrlRoot()}/files/microservice/api/{$microserviceApiEntity->getImage()}";
            
            $this->response['fileExists'] = file_exists("{$this->helper->getPathSrc()}/Controller/Microservice/Api/{$microserviceApiEntity->getName()}/{$microserviceApiEntity->getControllerName()}Controller.php");
            $this->response['logo'] = $logo;
            $this->response['values']['microserviceApiEntity'] = $microserviceApiEntity;
        }
        
        $form = $this->createForm(MicroserviceApiFormType::class, $microserviceApiEntity, Array(
            'validation_groups' => Array("microservice_api_profile")
        ));
        $form->handleRequest($request);
        
        if ($request->isMethod("POST") == true && $checkUserRole == true) {
            if ($form->isSubmitted() == true && $form->isValid() == true) {
                $this->fileUpload($form, $microserviceApiEntity);
                
                $this->entityManager->persist($microserviceApiEntity);
                $this->entityManager->flush();
                
                $this->response['messages']['success'] = $this->helper->getTranslator()->trans("microserviceApiController_3");
            }
            else {
                $this->response['messages']['error'] = $this->helper->getTranslator()->trans("microserviceApiController_4");
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
    
    // Functions private
    private function fileUpload($form, $entity) {
        $microserviceApiRow = $this->query->selectMicroserviceApiDatabase($this->session->get("microserviceApiProfileId"), true);
        
        $pathImage = "{$this->helper->getPathPublic()}/files/microservice/api";
        
        $image = $entity->getImage();
        
        // Remove image
        if ($form->get("removeImage")->getData() == true) {
            if ($microserviceApiRow['image'] != "" && file_exists("{$pathImage}/{$microserviceApiRow['image']}") == true)
                unlink("{$pathImage}/{$microserviceApiRow['image']}");
            
            $entity->setImage(null);
        }
        else if ($microserviceApiRow['image'] != "")
            $entity->setImage($microserviceApiRow['image']);
        
        // Upload image
        if ($image != null && $form->get("removeImage")->getData() == false) {
            $fileName = $image->getClientOriginalName();
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $extension = $extension != "" ? ".{$extension}" : "";
            $newName = uniqid() . $extension;
            $image->move($pathImage, $newName);
            
            $entity->setImage($newName);
        }
    }
}