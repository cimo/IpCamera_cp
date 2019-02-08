<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ModuleFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_module";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\Module",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("position", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "moduleFormType_1",
            'data' => $options['data']->getPosition(),
            'choices' => Array(
                "moduleFormType_2" => 'left',
                "moduleFormType_3" => 'center',
                "moduleFormType_4" => 'right'
            )
        ))
        ->add("rankColumnSort", HiddenType::class, Array(
            'required' => true
        ))
        ->add("name", TextType::class, Array(
            'required' => true,
            'label' => "moduleFormType_5"
        ))
        ->add("label", TextType::class, Array(
            'required' => false,
            'label' => "moduleFormType_6"
        ))
        ->add("controllerName", TextType::class, Array(
            'required' => true,
            'label' => "moduleFormType_7"
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "moduleFormType_8",
            'choices' => Array(
                "moduleFormType_9" => "0",
                "moduleFormType_10" => "1"
            )
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "moduleFormType_11"
        ));
    }
}