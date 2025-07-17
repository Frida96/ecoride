<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreferencesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fumeur', ChoiceType::class, [
                'label' => 'Fumeur accepté',
                'choices' => [
                    'Oui, je accepte les fumeurs' => 'Oui',
                    'Non, fumeur interdit' => 'Non',
                ],
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('animaux', ChoiceType::class, [
                'label' => 'Animaux acceptés',
                'choices' => [
                    'Oui, j\'accepte les animaux' => 'Oui',
                    'Non, pas d\'animaux' => 'Non',
                ],
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('musique', ChoiceType::class, [
                'label' => 'Musique pendant le trajet',
                'choices' => [
                    'Oui, j\'aime la musique' => 'Oui',
                    'Non, je préfère le silence' => 'Non',
                ],
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('discussion', ChoiceType::class, [
                'label' => 'Discussion pendant le trajet',
                'choices' => [
                    'Oui, j\'aime discuter' => 'Oui',
                    'Non, je préfère le calme' => 'Non',
                ],
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('arretsPipi', ChoiceType::class, [
                'label' => 'Arrêts pause acceptés',
                'choices' => [
                    'Oui, pause possible' => 'Oui',
                    'Non, trajet direct' => 'Non',
                ],
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('preferencesLibres', TextareaType::class, [
                'label' => 'Autres préférences (optionnel)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Ex: Je préfère les trajets matinaux, j\'accepte les enfants...'
                ],
            ]);

        // Événement pour pré-remplir le formulaire avec les préférences existantes
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            if ($user && $user->getPreferences()) {
                $preferences = $this->parsePreferences($user->getPreferences());
                
                if (isset($preferences['Fumeur'])) {
                    $form->get('fumeur')->setData($preferences['Fumeur']);
                }
                if (isset($preferences['Animal'])) {
                    $form->get('animaux')->setData($preferences['Animal']);
                }
                if (isset($preferences['Musique'])) {
                    $form->get('musique')->setData($preferences['Musique']);
                }
                if (isset($preferences['Discussion'])) {
                    $form->get('discussion')->setData($preferences['Discussion']);
                }
                if (isset($preferences['Arrêts'])) {
                    $form->get('arretsPipi')->setData($preferences['Arrêts']);
                }
                if (isset($preferences['Autres'])) {
                    $form->get('preferencesLibres')->setData($preferences['Autres']);
                }
            }
        });

        // Événement pour formatter les préférences avant sauvegarde
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $user = $event->getData();
            $form = $event->getForm();

            $preferences = [];
            
            if ($form->get('fumeur')->getData()) {
                $preferences[] = 'Fumeur: ' . $form->get('fumeur')->getData();
            }
            if ($form->get('animaux')->getData()) {
                $preferences[] = 'Animal: ' . $form->get('animaux')->getData();
            }
            if ($form->get('musique')->getData()) {
                $preferences[] = 'Musique: ' . $form->get('musique')->getData();
            }
            if ($form->get('discussion')->getData()) {
                $preferences[] = 'Discussion: ' . $form->get('discussion')->getData();
            }
            if ($form->get('arretsPipi')->getData()) {
                $preferences[] = 'Arrêts: ' . $form->get('arretsPipi')->getData();
            }
            if ($form->get('preferencesLibres')->getData()) {
                $preferences[] = 'Autres: ' . $form->get('preferencesLibres')->getData();
            }

            $user->setPreferences(implode(', ', $preferences));
        });
    }

    private function parsePreferences(string $preferences): array
    {
        $parsed = [];
        $parts = explode(', ', $preferences);
        
        foreach ($parts as $part) {
            if (strpos($part, ': ') !== false) {
                [$key, $value] = explode(': ', $part, 2);
                $parsed[$key] = $value;
            }
        }
        
        return $parsed;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
