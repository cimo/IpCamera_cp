<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PageCommentFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_pageComment";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\PageComment",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("type", HiddenType::class, Array(
            'required' => true
        ))
        ->add("argument", TextareaType::class, Array(
            'required' => true,
            'label' => "pageCommentType_1"
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "pageCommentType_2",
        ));
    }
}