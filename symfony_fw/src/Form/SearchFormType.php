<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SearchFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_search";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Form\Model\SearchModel",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, Array $options) {
        $builder->add("words", TextType::class, Array(
            'required' => true,
            'attr' => Array(
                'placeholder' => "searchFormType_1",
                'autocomplete' => "off",
                'autocorrect' => "off",
                'spellcheck' => "false"
            )
        ));
    }
}