<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SettingFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_setting";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\Setting",
            'csrf_protection' => true,
            'validation_groups' => null,
            'template' => null,
            'language' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, Array $options) {
        $builder->add("template", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_1",
            'choices' => $options['template'],
        ))
        ->add("templateColumn", ChoiceType::class, Array(
            'required' => true,
            'choices' => Array(
                "settingFormType_2" => "1",
                "settingFormType_3" => "2",
                "settingFormType_4" => "3",
                "settingFormType_5" => "4"
            ),
            'expanded' => true,
            'multiple' => false
        ))
        ->add("pageDate", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_6",
            'choices' => Array(
                "settingFormType_7" => "0",
                "settingFormType_8" => "1"
            )
        ))
        ->add("pageComment", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_9",
            'choices' => Array(
                "settingFormType_7" => "0",
                "settingFormType_8" => "1"
            )
        ))
        ->add("pageCommentActive", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_10",
            'choices' => Array(
                "settingFormType_7" => "0",
                "settingFormType_8" => "1"
            )
        ))
        ->add("language", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_11",
            'choices' => $options['language'],
            'data' => $options['data']->getLanguage()
        ))
        ->add("emailAdmin", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_12"
        ))
        ->add("websiteActive", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_13",
            'choices' => Array(
                "settingFormType_7" => "0",
                "settingFormType_8" => "1"
            )
        ))
        ->add("roleUserId", HiddenType::class, Array(
            'required' => true
        ))
        ->add("https", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_14",
            'choices' => Array(
                "settingFormType_7" => "0",
                "settingFormType_8" => "1"
            )
        ))
        ->add("registrationUserConfirmAdmin", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_15",
            'choices' => Array(
                "settingFormType_7" => "0",
                "settingFormType_8" => "1"
            )
        ))
        ->add("loginAttemptTime", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_16"
        )) 
        ->add("loginAttemptCount", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_17"
        ))
        ->add("registration", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_18",
            'choices' => Array(
                "settingFormType_7" => "0",
                "settingFormType_8" => "1"
            )
        ))
        ->add("recoverPassword", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_19",
            'choices' => Array(
                "settingFormType_7" => "0",
                "settingFormType_8" => "1"
            )
        ))
        ->add("captcha", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_20",
            'choices' => Array(
                "settingFormType_7" => "0",
                "settingFormType_8" => "1"
            )
        ))
        ->add("useType", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_21",
            'choices' => Array(
                "settingFormType_22" => "1",
                "settingFormType_23" => "2"
            )
        ))
        ->add("secretPassphrase", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_24"
        ))
        ->add("payment", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_25",
            'choices' => Array(
                "settingFormType_7" => "0",
                "settingFormType_8" => "1"
            )
        ))
        ->add("credit", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_26",
            'choices' => Array(
                "settingFormType_7" => "0",
                "settingFormType_8" => "1"
            )
        ))
        ->add("payPalSandbox", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_27",
            'choices' => Array(
                "settingFormType_7" => "0",
                "settingFormType_8" => "1"
            )
        ))
        ->add("payPalBusiness", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_28"
        ))
        ->add("payPalCurrencyCode", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_29"
        ))
        ->add("payPalCreditAmount", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_30"
        ))
        ->add("javascriptMinify", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingFormType_31",
            'choices' => Array(
                "settingFormType_7" => "0",
                "settingFormType_8" => "1"
            )
        ))
        ->add("serverUser", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_32"
        ))
        ->add("serverRoot", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_33",
            'data' => $_SERVER['DOCUMENT_ROOT']
        ))
        ->add("serverHost", TextType::class, Array(
            'required' => true,
            'label' => "settingFormType_34",
            'data' => $_SERVER['HTTP_HOST']
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "settingFormType_35"
        ));
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $formEvent) {
            $data = $formEvent->getData();
            
            $payPalCurrencyCode = strtoupper($data->getPayPalCurrencyCode());
            $data->setPayPalCurrencyCode($payPalCurrencyCode);
            
            $formEvent->setData($data);
        });
    }
}