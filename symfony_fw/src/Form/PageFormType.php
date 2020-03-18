<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PageFormType extends AbstractType {
    public function getBlockPrefix() {
        return "form_page";
    }
    
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(Array(
            'data_class' => "App\Entity\Page",
            'csrf_protection' => true,
            'validation_groups' => null,
            'urlLocale' => null,
            'pageRow' => null,
            'parent' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, Array $options) {
        $builder->add("language", HiddenType::class, Array(
            'required' => true,
            'data' => $options['urlLocale']
        ))
        ->add("alias", TextType::class, Array(
            'required' => true,
            'label' => "pageFormType_1",
            'data' => $options['data']->getAlias()
        ))
        ->add("parent", ChoiceType::class, Array(
            'required' => false,
            'placeholder' => "pageFormType_2",
            'choices' => $options['parent']
        ))
        ->add("title", TextType::class, Array(
            'required' => false,
            'label' => "pageFormType_3",
            'data' => $options['pageRow']['title']
        ))
        ->add("controllerAction", TextType::class, Array(
            'required' => false,
            'label' => "pageFormType_4"
        ))
        ->add("argument", TextareaType::class, Array(
            'required' => false,
            'label' => "pageFormType_5",
            'data' => html_entity_decode($options['pageRow']['argument'], ENT_QUOTES, "UTF-8")
        ))
        ->add("roleUserId", HiddenType::class, Array(
            'required' => true,
            'data' => $options['data']->getRoleUserId()
        ))
        ->add("protected", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "pageFormType_6",
            'data' => $options['data']->getProtected(),
            'choices' => Array(
                "pageFormType_7" => "0",
                "pageFormType_8" => "1"
            )
        ))
        ->add("showInMenu", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "pageFormType_9",
            'data' => $options['data']->getShowInMenu(),
            'choices' => Array(
                "pageFormType_7" => "0",
                "pageFormType_8" => "1"
            )
        ))
        ->add("rankMenuSort", HiddenType::class, Array(
            'required' => true
        ))
        ->add("menuName", TextType::class, Array(
            'required' => true,
            'label' => "pageFormType_10",
            'data' => $options['pageRow']['menu_name']
        ))
        ->add("comment", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "pageFormType_11",
            'data' => $options['data']->getComment(),
            'choices' => Array(
                "pageFormType_7" => "0",
                "pageFormType_8" => "1"
            )
        ))
        ->add("onlyParent", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "pageFormType_12",
            'data' => $options['data']->getOnlyParent(),
            'choices' => Array(
                "pageFormType_7" => "0",
                "pageFormType_8" => "1"
            )
        ))
        ->add("onlyLink", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "pageFormType_13",
            'data' => $options['data']->getOnlyLink(),
            'choices' => Array(
                "pageFormType_7" => "0",
                "pageFormType_8" => "1"
            )
        ))
        ->add("link", TextType::class, Array(
            'required' => true,
            'label' => "pageFormType_14",
            'data' => $options['data']->getLink()
        ))
        ->add("metaDescription", TextareaType::class, Array(
            'required' => false,
            'label' => "pageFormType_15"
        ))
        ->add("metaKeywords", TextType::class, Array(
            'required' => false,
            'label' => "pageFormType_16"
        ))
        ->add("metaRobots", TextType::class, Array(
            'required' => false,
            'label' => "pageFormType_17"
        ))
        ->add("event", HiddenType::class, Array(
            'required' => true
        ))
        ->add("submit", SubmitType::class, Array(
            'label' => "pageFormType_18",
        ));
        
        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $formEvent) {
            $data = $formEvent->getData();
            
            if ($data->getDraft() != 0) {
                $alias = "{$data->getAlias()}_draft";
                
                $data->setAlias($alias);
            }
        });
    }
}