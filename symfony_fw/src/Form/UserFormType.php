<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
    
    public function buildForm(FormBuilderInterface $builder, Array $options) {
        $builder->add("roleUserId", HiddenType::class, Array(
            'required' => true
        ))
        ->add("image", FileType::class, Array(
            'required' => false,
            'label' => "userFormType_1",
            'data_class' => null
        ))
        ->add('removeImage', CheckboxType::class, Array(
            'required' => false,
            'label' => "userFormType_2"
        ))
        ->add("username", TextType::class, Array(
            'required' => true,
            'label' => "userFormType_3"
        ))
        ->add("name", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_4"
        ))
        ->add("surname", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_5"
        ))
        ->add("email", EmailType::class, Array(
            'required' => true,
            'label' => "userFormType_6"
        ))
        ->add("telephone", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_7"
        ))
        ->add("born", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_8"
        ))
        ->add("gender", ChoiceType::class, Array(
            'required' => false,
            'placeholder' => "userFormType_9",
            'choices' => Array(
                'userFormType_10' => "m",
                'userFormType_11' => "w"
            )
        ))
        ->add("fiscalCode", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_12"
        ))
        ->add("companyName", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_13"
        ))
        ->add("vat", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_14"
        ))
        ->add("website", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_15"
        ))
        ->add("state", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_16"
        ))
        ->add("city", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_17"
        ))
        ->add("zip", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_18"
        ))
        ->add("address", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_19"
        ))
        ->add("password", PasswordType::class, Array(
            'required' => true,
            'label' => "userFormType_20"
        ))
        ->add("passwordConfirm", PasswordType::class, Array(
            'required' => true,
            'label' => "userFormType_21"
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "userFormType_22",
            'choices' => Array(
                "userFormType_23" => "0",
                "userFormType_24" => "1"
            )
        ))
        ->add("credit", TextType::class, Array(
            'required' => false,
            'label' => "userFormType_25"
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "userFormType_26"
        ));
        
        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $formEvent) {
            $data = $formEvent->getData();
            
            if ($data->getRemoveImage() == false)
                $data->setRemoveImage("0");
        });
    }
}