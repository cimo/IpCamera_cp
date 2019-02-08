<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SettingLinePushFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_settingLinePush";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\SettingLinePush",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("name", TextType::class, Array(
            'required' => true,
            'label' => "settingLinePushFormType_1"
        ))
        ->add("userIdPrimary", TextType::class, Array(
            'required' => true,
            'label' => "settingLinePushFormType_2"
        ))
        ->add("accessToken", TextType::class, Array(
            'required' => true,
            'label' => "settingLinePushFormType_3"
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "settingLinePushFormType_4",
            'choices' => Array(
                "settingLinePushFormType_5" => "0",
                "settingLinePushFormType_6" => "1"
            )
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "settingLinePushFormType_7"
        ));
    }
}