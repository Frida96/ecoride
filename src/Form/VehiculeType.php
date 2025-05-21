<?php
namespace App\Form;

use App\Entity\Vehicule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehiculeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('marque', TextType::class, [
                'label' => 'Marque',
                'attr' => [
                    'placeholder' => 'Ex: Renault'
                ]
            ])
            ->add('modele', TextType::class, [
                'label' => 'Modèle',
                'attr' => [
                    'placeholder' => 'Ex: Clio'
                ]
            ])
            ->add('immatriculation', TextType::class, [
                'label' => 'Immatriculation',
                'attr' => [
                    'placeholder' => 'Ex: AB-123-CD'
                ]
            ])
            ->add('couleur', TextType::class, [
                'label' => 'Couleur',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: Rouge'
                ]
            ])
            ->add('places', IntegerType::class, [
                'label' => 'Nombre de places (conducteur inclus)',
                'attr' => [
                    'min' => 2,
                    'max' => 9
                ]
            ])
            ->add('energie', ChoiceType::class, [
                'label' => 'Type d\'énergie',
                'choices' => [
                    'Essence' => 'Essence',
                    'Diesel' => 'Diesel',
                    'Électrique' => 'Électrique',
                    'Hybride' => 'Hybride',
                    'GPL' => 'GPL',
                    'Autre' => 'Autre'
                ]
            ])
            ->add('estEcologique', CheckboxType::class, [
                'label' => 'Ce véhicule est écologique',
                'required' => false,
                'help' => 'Cochez cette case si votre véhicule est électrique, hybride ou si vous pratiquez l\'éco-conduite'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-success w-100'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
}