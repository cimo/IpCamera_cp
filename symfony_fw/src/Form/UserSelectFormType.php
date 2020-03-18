<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserSelectFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_user_select";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\User",
            'csrf_protection' => true,
            'validation_groups' => null,
            'id' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, Array $options) {
        $builder->add("id", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "userSelectFormType_1",
            'choices' => $options['id']
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "userSelectFormType_2"
        ));
    }
}