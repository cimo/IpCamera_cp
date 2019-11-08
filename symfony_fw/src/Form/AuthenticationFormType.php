<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AuthenticationFormType extends AbstractType {
    public function getBlockPrefix() {
        return null;
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'csrf_protection' => true
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, Array $options) {
        $builder->add("_username", TextType::class, Array(
            'required' => true,
            'label' => "authenticationFomType_1"
        ))
        ->add("_password", PasswordType::class, Array(
            'required' => true,
            'label' => "authenticationFomType_2"
        ))
        ->add("_remember_me", CheckboxType::class, Array(
            'required' => false,
            'label' => "authenticationFomType_3"
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "authenticationFomType_4"
        ));
    }
}