<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_user";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\User",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("roleUserId", HiddenType::class, Array(
            'required' => true
        ))
        ->add("username", TextType::class, Array(
            'required' => true,
            'label' => "userFormType_1"
        ))
        ->add("name", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_2"
        ))
        ->add("surname", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_3"
        ))
        ->add("email", EmailType::class, Array(
            'required' => true,
            'label' => "userFormType_4"
        ))
        ->add("telephone", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_5"
        ))
        ->add("born", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_6"
        ))
        ->add("gender", ChoiceType::class, Array(
            'required' => false,
            'placeholder' => "userFormType_7",
            'choices' => Array(
                'userFormType_8' => "m",
                'userFormType_9' => "w"
            )
        ))
        ->add("fiscalCode", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_10"
        ))
        ->add("companyName", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_11"
        ))
        ->add("vat", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_12"
        ))
        ->add("website", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_13"
        ))
        ->add("state", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_14"
        ))
        ->add("city", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_15"
        ))
        ->add("zip", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_16"
        ))
        ->add("address", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_17"
        ))
        ->add("password", PasswordType::class, Array(
            'required' => true,
            'label' => "userFormType_18"
        ))
        ->add("passwordConfirm", PasswordType::class, Array(
            'required' => true,
            'label' => "userFormType_19"
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "userFormType_20",
            'choices' => Array(
                "userFormType_21" => "0",
                "userFormType_22" => "1"
            )
        ))
        ->add("credit", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_23"
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "userFormType_24"
        ));
    }
}