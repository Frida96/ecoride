<?php

namespace App\Form;

use App\Entity\Avis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('note', ChoiceType::class, [
                'label' => 'Note (optionnel)',
                'choices' => [
                    'Choisir une note' => null,
                    '⭐ 1 étoile - Très décevant' => 1,
                    '⭐⭐ 2 étoiles - Décevant' => 2,
                    '⭐⭐⭐ 3 étoiles - Correct' => 3,
                    '⭐⭐⭐⭐ 4 étoiles - Bien' => 4,
                    '⭐⭐⭐⭐⭐ 5 étoiles - Excellent' => 5,
                ],
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire (optionnel)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Partagez votre expérience avec ce chauffeur...'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
        ]);
    }
}
