<?php

namespace App\Form;

use App\Entity\Vehicle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class VehicleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('immatriculation', TextType::class, [
                'label' => 'Plaque d\'immatriculation',
                'attr' => [
                    'placeholder' => 'AB-123-CD',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une plaque d\'immatriculation',
                    ]),
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'La plaque ne peut pas dépasser {{ limit }} caractères',
                    ]),
                    new Regex([
                        'pattern' => '/^[A-Z0-9\-]+$/',
                        'message' => 'Format invalide. Utilisez des lettres majuscules, chiffres et tirets.',
                    ]),
                ],
            ])
            ->add('marque', ChoiceType::class, [
                'label' => 'Marque',
                'choices' => [
                    'Choisissez une marque' => '',
                    'Audi' => 'Audi',
                    'BMW' => 'BMW',
                    'Citroën' => 'Citroën',
                    'Fiat' => 'Fiat',
                    'Ford' => 'Ford',
                    'Honda' => 'Honda',
                    'Hyundai' => 'Hyundai',
                    'Kia' => 'Kia',
                    'Mercedes' => 'Mercedes',
                    'Nissan' => 'Nissan',
                    'Opel' => 'Opel',
                    'Peugeot' => 'Peugeot',
                    'Renault' => 'Renault',
                    'Seat' => 'Seat',
                    'Skoda' => 'Skoda',
                    'Tesla' => 'Tesla',
                    'Toyota' => 'Toyota',
                    'Volkswagen' => 'Volkswagen',
                    'Volvo' => 'Volvo',
                    'Autre' => 'Autre',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une marque',
                    ]),
                ],
            ])
            ->add('modele', TextType::class, [
                'label' => 'Modèle',
                'attr' => [
                    'placeholder' => 'ex: 308, Golf, Clio...',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer le modèle',
                    ]),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Le modèle ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('couleur', ChoiceType::class, [
                'label' => 'Couleur',
                'choices' => [
                    'Choisissez une couleur' => '',
                    'Blanc' => 'Blanc',
                    'Noir' => 'Noir',
                    'Gris' => 'Gris',
                    'Rouge' => 'Rouge',
                    'Bleu' => 'Bleu',
                    'Vert' => 'Vert',
                    'Jaune' => 'Jaune',
                    'Orange' => 'Orange',
                    'Marron' => 'Marron',
                    'Violet' => 'Violet',
                    'Beige' => 'Beige',
                    'Argent' => 'Argent',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une couleur',
                    ]),
                ],
            ])
            ->add('energie', ChoiceType::class, [
                'label' => 'Type d\'énergie',
                'choices' => [
                    'Électrique (Écologique ⚡)' => 'electrique',
                    'Essence' => 'essence',
                    'Diesel' => 'diesel',
                    'Hybride' => 'hybride',
                    'GPL' => 'gpl',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner le type d\'énergie',
                    ]),
                ],
            ])
            ->add('datePremierImmatriculation', DateType::class, [
                'label' => 'Date de première immatriculation',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer la date de première immatriculation',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicle::class,
        ]);
    }
}