<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CreditFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_credit";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Form\Model\CreditModel",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("credit", TextType::class, Array(
            'required' => true,
            'label' => "creditFormType_1"
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "creditFormType_2"
        ));
    }
}