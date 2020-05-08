<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MicroserviceApiFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_microservice_api";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\MicroserviceApi",
            'csrf_protection' => true,
            'validation_groups' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, Array $options) {
        $builder->add("name", TextType::class, Array(
            'required' => true,
            'label' => "microserviceApiFormType_1"
        ))
        ->add("controllerName", TextType::class, Array(
            'required' => true,
            'label' => "microserviceApiFormType_2"
        ))
        ->add("description", TextType::class, Array(
            'required' => true,
            'label' => "microserviceApiFormType_3"
        ))
        ->add("image", FileType::class, Array(
            'required' => false,
            'label' => "",
            'data_class' => null
        ))
        ->add('removeImage', CheckboxType::class, Array(
            'required' => false,
            'label' => "microserviceApiFormType_5"
        ))
        ->add("active", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "microserviceApiFormType_6",
            'choices' => Array(
                "microserviceApiFormType_7" => "0",
                "microserviceApiFormType_8" => "1"
            )
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "microserviceApiFormType_9"
        ));
        
        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $formEvent) {
            $data = $formEvent->getData();
            
            if ($data->getRemoveImage() == false)
                $data->setRemoveImage("0");
        });
    }
}