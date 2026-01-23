<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Formation;
use App\Entity\Playlist;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Titre : obligatoire
            ->add('title', TextType::class, [
                'label'       => 'Titre',
                'constraints' => [
                    new NotBlank(['message' => 'Le titre est obligatoire.']),
                ],
            ])

            // Description : facultative
            ->add('description', TextType::class, [
                'label'    => 'Description',
                'required' => false,
            ])

            // Date de publication : obligatoire, pas postérieure à aujourd’hui
            ->add('publishedAt', DateType::class, [
                'label'  => 'Date de publication',
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La date est obligatoire.']),
                    new LessThanOrEqual([
                        'value'   => 'today',
                        'message' => 'La date ne doit pas être postérieure à la date du jour.',
                    ]),
                ],
            ])

            // Playlist : une seule, obligatoire
            ->add('playlist', EntityType::class, [
                'label'        => 'Playlist',
                'class'        => Playlist::class,
                'choice_label' => 'name',
                'placeholder'  => 'Choisir une playlist',
                'required'     => true,
            ])

            // Catégories : plusieurs possibles, facultatif
            ->add('categories', EntityType::class, [
                'label'        => 'Catégories',
                'class'        => Categorie::class,
                'choice_label' => 'name',
                'multiple'     => true,
                'expanded'     => false,
                'required'     => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
