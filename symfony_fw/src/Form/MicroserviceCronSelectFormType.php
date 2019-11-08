<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MicroserviceCronSelectFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_microservice_cron_select";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Form\Model\MicroserviceCronSelectModel",
            'csrf_protection' => true,
            'csrf_token_id' => "intention",
            'validation_groups' => null,
            'choicesId' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, Array $options) {
        $builder->add("id", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "microserviceCronSelectFormType_1",
            'choices' => $options['choicesId']
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "microserviceCronSelectFormType_2"
        ));
    }
}