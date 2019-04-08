<?php
namespace App\Controller\MyPage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\Classes\System\Utility;
use App\Classes\System\Ajax;
use App\Classes\System\TableAndPagination;

use App\Form\PaymentSelectFormType;

class MyPagePaymentController extends AbstractController {
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
    
    // Properties
    
    // Functions public
    /**
    * @Route(
    *   name = "myPage_payment_select",
    *   path = "/myPage_payment_select/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/my_page/myPage_payment_select.html.twig")
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
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_USER"), $this->getUser());
        
        $settingRow = $this->query->selectSettingDatabase();
        
        if ($settingRow['payment'] == true) {
            $_SESSION['paymentProfileId'] = 0;

            $paymentRows = $this->query->selectAllPaymentDatabase($this->getUser()->getId());

            $tableAndPagination = $this->tableAndPagination->request($paymentRows, 20, "payment", true);

            $this->response['values']['search'] = $tableAndPagination['search'];
            $this->response['values']['pagination'] = $tableAndPagination['pagination'];
            $this->response['values']['listHtml'] = $this->createListHtml($tableAndPagination['listHtml']);
            $this->response['values']['count'] = $tableAndPagination['count'];

            $form = $this->createForm(PaymentSelectFormType::class, null, Array(
                'validation_groups' => Array('payment_select'),
                'choicesId' => array_reverse(array_column($paymentRows, "id", "transaction"), true)
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
        else
            return new Response();
    }
    
    /**
    * @Route(
    *   name = "myPage_payment_profile",
    *   path = "/myPage_payment_profile/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/my_page/myPage_payment_profile.html.twig")
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
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_USER"), $this->getUser());
        
        $settingRow = $this->query->selectSettingDatabase();
        
        if ($settingRow['payment'] == true) {
            if ($request->isMethod("POST") == true && $checkUserRole == true) {
                if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                    $id = $request->get("id");
                    
                    $paymentEntity = $this->entityManager->getRepository("App\Entity\Payment")->find($id);

                    if ($paymentEntity != null) {
                        $_SESSION['paymentProfileId'] = $id;

                        $this->response['values']['payment'] = $paymentEntity;

                        $this->response['render'] = $this->renderView("@templateRoot/render/my_page/myPage_payment_profile.html.twig", Array(
                            'urlLocale' => $this->urlLocale,
                            'urlCurrentPageId' => $this->urlCurrentPageId,
                            'urlExtra' => $this->urlExtra,
                            'response' => $this->response
                        ));
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("myPagePaymentController_1");
                }
            }
            
            return $this->ajax->response(Array(
                'urlLocale' => $this->urlLocale,
                'urlCurrentPageId' => $this->urlCurrentPageId,
                'urlExtra' => $this->urlExtra,
                'response' => $this->response
            ));
        }
        else
            return new Response();
    }
    
    /**
    * @Route(
    *   name = "myPage_payment_delete",
    *   path = "/myPage_payment_delete/{_locale}/{urlCurrentPageId}/{urlExtra}",
    *   defaults = {"_locale" = "%locale%", "urlCurrentPageId" = "2", "urlExtra" = ""},
    *   requirements = {"_locale" = "[a-z]{2}", "urlCurrentPageId" = "\d+", "urlExtra" = "[^/]+"},
    *	methods={"POST"}
    * )
    * @Template("@templateRoot/render/my_page/myPage_payment_delete.html.twig")
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
        $checkUserRole = $this->utility->checkUserRole(Array("ROLE_USER"), $this->getUser());
        
        $settingRow = $this->query->selectSettingDatabase();
        
        if ($settingRow['payment'] == true) {
            if ($request->isMethod("POST") == true && $checkUserRole == true) {
                if ($this->isCsrfTokenValid("intention", $request->get("token")) == true) {
                    if ($request->get("event") == "delete") {
                        $id = $request->get("id") == null ? $_SESSION['paymentProfileId'] : $request->get("id");

                        $paymentDatabase = $this->paymentDatabase("delete", $id);

                        if ($paymentDatabase == true) {
                            $this->response['values']['id'] = $id;

                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("myPagePaymentController_2");
                        }
                    }
                    else if ($request->get("event") == "deleteAll") {
                        $paymentDatabase = $this->paymentDatabase("deleteAll", null);

                        if ($paymentDatabase == true)
                            $this->response['messages']['success'] = $this->utility->getTranslator()->trans("myPagePaymentController_3");
                    }
                    else
                        $this->response['messages']['error'] = $this->utility->getTranslator()->trans("myPagePaymentController_4");

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
        else
            return new Response();
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
                    {$value['transaction']}
                </td>
                <td>
                    {$value['date']}
                </td>
                <td>
                    {$value['status']}
                </td>
                <td>
                    {$value['payer']}
                </td>
                <td>
                    <button class=\"mdc-fab mdc-fab--mini myPage_payment_delete\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">delete</span></button>
                </td>
            </tr>";
        }
        
        return $listHtml;
    }
    
    private function paymentDatabase($type, $id) {
        if ($type == "delete") {
            $query = $this->utility->getConnection()->prepare("UPDATE payments
                                                                SET status_delete = :statusDelete
                                                                WHERE user_id = :userId
                                                                AND id = :id");
            
            $query->bindValue(":statusDelete", 1);
            $query->bindValue(":userId", $_SESSION['paymentUserId']);
            $query->bindValue(":id", $id);
            
            return $query->execute();
        }
        else if ($type == "deleteAll") {
            $query = $this->utility->getConnection()->prepare("UPDATE payments
                                                                SET status_delete = :statusDelete
                                                                WHERE user_id = :userId");
            
            $query->bindValue(":statusDelete", 1);
            $query->bindValue(":userId", $_SESSION['paymentUserId']);
            
            return $query->execute();
        }
    }
}