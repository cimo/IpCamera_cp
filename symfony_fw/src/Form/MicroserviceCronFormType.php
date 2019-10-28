<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MicroserviceCronFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_microservice_cron";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\MicroserviceCron",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("name", TextType::class, Array(
            'required' => true,
            'label' => "microserviceCronFormType_1"
        ))
        ->add("time", TextType::class, Array(
            'required' => true,
            'label' => "microserviceCronFormType_2"
        ))
        ->add("code", TextareaType::class, Array(
            'required' => true,
            'label' => "microserviceCronFormType_3"
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "microserviceCronFormType_5"
        ));
    }
}