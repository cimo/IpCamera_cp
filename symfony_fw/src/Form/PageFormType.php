<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
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
            'choicesParent' => null
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $values = Array();
        
        if ($options['data']->getId() == null) {
            $values = Array(
                'alias' => "",
                'title' => "",
                'argument' => "",
                'roleUser_id' => "1,2,",
                'protected' => "0",
                'show_in_menu' => "1",
                'id' => "0",
                'menu_name' => "-",
                'comment' => "1",
                'only_parent' => "0",
                'only_link' => "0",
                'link' => "-"
            );
        }
        else {
            $values = Array(
                'alias' => $options['data']->getAlias(),
                'title' => $options['pageRow']['title'],
                'argument' => $options['pageRow']['argument'],
                'roleUser_id' => $options['data']->getRoleUserId(),
                'protected' => $options['data']->getProtected(),
                'show_in_menu' => $options['data']->getShowInMenu(),
                'id' => $options['data']->getId(),
                'menu_name' => $options['pageRow']['menu_name'],
                'comment' => $options['data']->getComment(),
                'only_parent' => $options['data']->getOnlyParent(),
                'only_link' => $options['data']->getOnlyLink(),
                'link' => $options['data']->getLink()
            );
        }
        
        $builder->add("language", HiddenType::class, Array(
            'required' => true,
            'data' => $options['urlLocale']
        ))
        ->add("alias", TextType::class, Array(
            'required' => true,
            'label' => "pageFormType_1",
            'data' => $values['alias']
        ))
        ->add("parent", ChoiceType::class, Array(
            'required' => false,
            'placeholder' => "pageFormType_2",
            'choices' => $options['choicesParent']
        ))
        ->add("title", TextType::class, Array(
            'required' => false,
            'label' => "pageFormType_3",
            'data' => $values['title']
        ))
        ->add("controllerAction", TextType::class, Array(
            'required' => false,
            'label' => "pageFormType_4"
        ))
        ->add("argument", TextareaType::class, Array(
            'required' => false,
            'label' => "pageFormType_5",
            'data' => html_entity_decode($values['argument'], ENT_QUOTES, "UTF-8")
        ))
        ->add("roleUserId", HiddenType::class, Array(
            'required' => true,
            'data' => $values['roleUser_id']
        ))
        ->add("protected", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "pageFormType_6",
            'data' => $values['protected'],
            'choices' => Array(
                "pageFormType_7" => "0",
                "pageFormType_8" => "1"
            )
        ))
        ->add("showInMenu", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "pageFormType_9",
            'data' => $values['show_in_menu'],
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
            'data' => $values['menu_name']
        ))
        ->add("comment", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "pageFormType_11",
            'data' => $values['comment'],
            'choices' => Array(
                "pageFormType_7" => "0",
                "pageFormType_8" => "1"
            )
        ))
        ->add("onlyParent", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "pageFormType_12",
            'data' => $values['only_parent'],
            'choices' => Array(
                "pageFormType_7" => "0",
                "pageFormType_8" => "1"
            )
        ))
        ->add("onlyLink", ChoiceType::class, Array(
            'required' => true,
            'placeholder' => "pageFormType_13",
            'data' => $values['only_link'],
            'choices' => Array(
                "pageFormType_7" => "0",
                "pageFormType_8" => "1"
            )
        ))
        ->add("link", TextType::class, Array(
            'required' => true,
            'label' => "pageFormType_14",
            'data' => $values['link']
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
        ->add("submit", SubmitType::class, Array(
            'label' => "pageFormType_18",
        ));
    }
}