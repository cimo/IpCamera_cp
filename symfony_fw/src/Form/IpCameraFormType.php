<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class IpCameraFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_ipCamera";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\IpCamera",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("name", TextType::class, Array(
            'required' => true,
            'label' => "ipCameraFormType_1"
        ))  
        ->add("userId", HiddenType::class, Array(
            'required' => true,
            'data' => $options['data']->getUserId()
        ))
        ->add("hostVideo", TextType::class, Array(
            'required' => true,
            'label' => "ipCameraFormType_2"
        ))
        ->add("hostImage", TextType::class, Array(
            'required' => false,
            'label' => "ipCameraFormType_3"
        ))
        ->add("username", TextType::class, Array(
            'required' => true,
            'label' => "ipCameraFormType_4"
        ))
        ->add("password", PasswordType::class, Array(
            'required' => true,
            'label' => "ipCameraFormType_5"
        ))
        ->add("detectionSensitivity", TextType::class, Array(
            'required' => true,
            'label' => "ipCameraFormType_6"
        ))
        ->add("detectionActive", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "ipCameraFormType_7",
            'choices' => Array(
                "ipCameraFormType_8" => "0",
                "ipCameraFormType_9" => "1"
            )
        ))
        ->add("detectionPid", TextType::class, Array(
            'required' => false,
            'label' => "ipCameraFormType_10",
            'attr' => Array(
                'readonly' => true
            )
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "ipCameraFormType_11",
            'choices' => Array(
                "ipCameraFormType_8" => "0",
                "ipCameraFormType_9" => "1"
            )
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "ipCameraFormType_12"
        ));
    }
}