<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RecoverPasswordFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_recover_password";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Form\Model\RecoverPasswordModel",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("email", EmailType::class, Array(
            'required' => true,
            'label' => "recoverPasswordFormType_1"
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "recoverPasswordFormType_2"
        ));
    }
}