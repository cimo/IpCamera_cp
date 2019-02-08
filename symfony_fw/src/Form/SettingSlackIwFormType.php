<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SettingSlackIwFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_settingSlackIw";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\SettingSlackIw",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("name", TextType::class, Array(
            'required' => true,
            'label' => "settingSlackIwFormType_1"
        ))
        ->add("hook", TextType::class, Array(
            'required' => true,
            'label' => "settingSlackIwFormType_2"
        ))
        ->add("channel", TextType::class, Array(
            'required' => true,
            'label' => "settingSlackIwFormType_3"
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
            'label' => "settingSlackIwFormType_7"
        ));
    }
}