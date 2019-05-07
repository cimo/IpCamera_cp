<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RoleUserFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_role";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\RoleUser",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add("level", TextType::class, Array(
            'required' => true,
            'label' => "roleUserFormType_1"
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "roleUserFormType_2"
        ));
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $formEvent) {
            $data = $formEvent->getData();
            
            $level = strtoupper($data->getLevel());
            $data->setLevel($level);
            
            $formEvent->setData($data);
        });
    }
}