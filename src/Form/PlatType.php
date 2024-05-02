<?php

namespace App\Form;


use App\Entity\Plat;
use App\Entity\Category;
use App\Entity\Gerant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class PlatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('prix')
            ->add('image', FileType::class, [
                'label' => 'Image (JPG, JPEG, PNG file)',
                'required' => false,
                'mapped' => true, // Mapper le champ avec une propriété de l'entité
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid JPG, JPEG or PNG file',
                    ])
                ],
            ])

            // Ajoutez un champ pour sélectionner une catégorie
            ->add('idCategory', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'type',
                'placeholder' => 'Select a category',
            ])
            // Ajoutez un champ pour sélectionner un restaurant (gerant)
            ->add('idRestaurant', EntityType::class, [
                'class' => Gerant::class,
                'choice_label' => 'name', // Remplacez 'type' par la propriété correcte de l'entité Gerant
                'placeholder' => 'Select a restaurant',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plat::class,
        ]);
    }
}


