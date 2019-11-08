<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PasswordFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_password";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Form\Model\PasswordModel",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, Array $options) {
        $builder->add("old", PasswordType::class, Array(
            'required' => true,
            'label' => "passwordFormType_1"
        ))
        ->add("new", PasswordType::class, Array(
            'required' => true,
            'label' => "passwordFormType_2"
        ))
        ->add("newConfirm", PasswordType::class, Array(
            'required' => true,
            'label' => "passwordFormType_3"
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "passwordFormType_4"
        ));
    }
}