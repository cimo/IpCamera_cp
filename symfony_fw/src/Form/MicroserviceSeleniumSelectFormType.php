<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class MicroserviceSeleniumSelectFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_microservice_selenium_select";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Form\Model\MicroserviceSeleniumSelectModel",
            'csrf_protection' => true,
            'csrf_token_id' => "intention",
            'validation_groups' => null,
            'choicesId' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, Array $options) {
        $builder->add("id", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "microserviceSeleniumSelectFormType_1",
            'choices' => $options['choicesId']
        ))
        ->add("name", HiddenType::class, Array(
            'required' => true
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "microserviceSeleniumSelectFormType_2"
        ));
    }
}