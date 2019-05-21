<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MicroserviceUnitTestFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_microservice_unit_test";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\MicroserviceUnitTest",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("name", TextType::class, Array(
            'required' => true,
            'label' => "microserviceUnitTestFormType_1"
        ))
        ->add("origin", TextareaType::class, Array(
            'required' => true,
            'label' => "microserviceUnitTestFormType_2"
        ))   
        ->add("code", TextareaType::class, Array(
            'required' => true,
            'label' => "microserviceUnitTestFormType_3"
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "microserviceUnitTestFormType_4",
            'choices' => Array(
                "microserviceUnitTestFormType_5" => "0",
                "microserviceUnitTestFormType_6" => "1"
            )
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "microserviceUnitTestFormType_7"
        ));
    }
}