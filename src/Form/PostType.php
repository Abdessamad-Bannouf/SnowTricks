<?php

namespace App\Form;

use App\Entity\Group;
use App\Entity\Post;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => "Titre de l'article"
                ]
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => "Description de l'article",
                    'class' => 'form-control'
                ]
            ])
            ->add('photo', FileType::class, [
                'label' => false,
                'multiple' => false,
                'mapped' => false, 
                'required' => false
            ])
            ->add('photos', FileType::class, [
                'label' => false,
                'multiple' => true,
                'mapped' => false, 
                'required' => false
            ])
            ->add('video', TextType::class, [
                'attr' => [
                    'placeholder' => "Vidéo de l'article",
                    'class' => 'form-control'
                ]
            ])
            ->add('group', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
                'multiple' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
