<?php

namespace App\Form;

use App\Entity\Trajet;
use App\Entity\Vehicle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class TrajetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lieuDepart', TextType::class, [
                'label' => 'Lieu de départ',
                'attr' => [
                    'placeholder' => 'Ville de départ (ex: Paris, Lyon...)',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un lieu de départ',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le lieu de départ doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'Le lieu de départ ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('lieuArrivee', TextType::class, [
                'label' => 'Lieu d\'arrivée',
                'attr' => [
                    'placeholder' => 'Ville d\'arrivée (ex: Marseille, Bordeaux...)',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un lieu d\'arrivée',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le lieu d\'arrivée doit faire au moins {{ limit }} caractères',
                        'maxMessage' => 'Le lieu d\'arrivée ne peut pas dépasser {{ limit }} caractères',
                    ]),
                ],
            ])
            ->add('dateDepart', DateTimeType::class, [
                'label' => 'Date et heure de départ',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime('+1 hour'))->format('Y-m-d\TH:i'),
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une date de départ',
                    ]),
                    new GreaterThan([
                        'value' => new \DateTime(),
                        'message' => 'La date de départ doit être dans le futur',
                    ]),
                ],
            ])
            ->add('dateArrivee', DateTimeType::class, [
                'label' => 'Date et heure d\'arrivée (estimée)',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer une date d\'arrivée',
                    ]),
                ],
            ])
            ->add('nbPlaces', IntegerType::class, [
                'label' => 'Nombre de places disponibles',
                'attr' => [
                    'placeholder' => '1-7 places',
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 7
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer le nombre de places',
                    ]),
                    new Range([
                        'min' => 1,
                        'max' => 7,
                        'notInRangeMessage' => 'Le nombre de places doit être entre {{ min }} et {{ max }}',
                    ]),
                ],
            ])
            ->add('prix', IntegerType::class, [
                'label' => 'Prix par passager (en crédits)',
                'attr' => [
                    'placeholder' => 'Prix en crédits',
                    'class' => 'form-control',
                    'min' => 3,
                    'max' => 100
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un prix',
                    ]),
                    new Range([
                        'min' => 3,
                        'max' => 100,
                        'notInRangeMessage' => 'Le prix doit être entre {{ min }} et {{ max }} crédits',
                    ]),
                ],
            ])
            ->add('vehicule', EntityType::class, [
                'class' => Vehicle::class,
                'choice_label' => function (Vehicle $vehicle) {
                    $eco = $vehicle->getEnergie() === 'electrique' ? ' ⚡' : '';
                    return $vehicle->getMarque() . ' ' . $vehicle->getModele() . ' (' . $vehicle->getCouleur() . ')' . $eco;
                },
                'label' => 'Véhicule utilisé',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un véhicule',
                    ]),
                ],
                'placeholder' => 'Choisissez votre véhicule',
            ]);

        // Filtrer les véhicules selon l'utilisateur connecté
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $trajet = $event->getData();
            $form = $event->getForm();

            if ($trajet && $trajet->getChauffeur()) {
                $user = $trajet->getChauffeur();
                
                $form->add('vehicule', EntityType::class, [
                    'class' => Vehicle::class,
                    'choice_label' => function (Vehicle $vehicle) {
                        $eco = $vehicle->getEnergie() === 'electrique' ? ' ⚡' : '';
                        return $vehicle->getMarque() . ' ' . $vehicle->getModele() . ' (' . $vehicle->getCouleur() . ')' . $eco;
                    },
                    'label' => 'Véhicule utilisé',
                    'attr' => ['class' => 'form-control'],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez sélectionner un véhicule',
                        ]),
                    ],
                    'choices' => $user->getVehicles(),
                    'placeholder' => 'Choisissez votre véhicule',
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trajet::class,
        ]);
    }
}
