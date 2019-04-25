<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ApiBasicFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_apiBasic";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\ApiBasic",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("name", TextType::class, Array(
            'required' => true,
            'label' => "apiBasicFormType_1"
        ))
        ->add("tokenName", TextType::class, Array(
            'required' => true,
            'label' => "apiBasicFormType_2"
        ))
        ->add("ip", TextareaType::class, Array(
            'required' => false,
            'label' => "apiBasicFormType_3"
        ))
        ->add("urlCallback", TextType::class, Array(
            'required' => false,
            'label' => "apiBasicFormType_4"
        ))
        ->add("databaseIp", TextType::class, Array(
            'required' => false,
            'label' => "apiBasicFormType_5"
        ))
        ->add("databaseName", TextType::class, Array(
            'required' => false,
            'label' => "apiBasicFormType_6"
        ))
        ->add("databaseUsername", TextType::class, Array(
            'required' => false,
            'label' => "apiBasicFormType_7"
        ))
        ->add("databasePassword", PasswordType::class, Array(
            'required' => false,
            'label' => "apiBasicFormType_8",
            'always_empty' => false
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "apiBasicFormType_9",
            'choices' => Array(
                "apiBasicFormType_10" => "0",
                "apiBasicFormType_11" => "1"
            )
        ))
        ->add("slackActive", CheckboxType::class, Array(
            'required' => false,
            'label' => "apiBasicFormType_12"
        ))
        ->add("lineActive", CheckboxType::class, Array(
            'required' => false,
            'label' => "apiBasicFormType_13"
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "apiBasicFormType_14"
        ));
        
        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $formEvent) {
            $data = $formEvent->getData();
            
            if ($data->getSlackActive() == false)
                $data->setSlackActive("0");
            
            if ($data->getLineActive() == false)
                $data->setLineActive("0");
        });
    }
}