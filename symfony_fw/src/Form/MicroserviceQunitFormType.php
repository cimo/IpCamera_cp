<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MicroserviceQunitFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_microservice_qunit";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\MicroserviceQunit",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, Array $options) {
        $builder->add("name", TextType::class, Array(
            'required' => true,
            'label' => "microserviceQunitFormType_1"
        ))
        ->add("origin", TextareaType::class, Array(
            'required' => true,
            'label' => "microserviceQunitFormType_2"
        ))   
        ->add("code", TextareaType::class, Array(
            'required' => true,
            'label' => "microserviceQunitFormType_3"
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "microserviceQunitFormType_4",
            'choices' => Array(
                "microserviceQunitFormType_5" => "0",
                "microserviceQunitFormType_6" => "1"
            )
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "microserviceQunitFormType_7"
        ));
    }
}