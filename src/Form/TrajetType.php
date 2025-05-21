<?php
namespace App\Form;

use App\Entity\Trajet;
use App\Entity\Vehicule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;

class TrajetType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();
        
        $builder
            ->add('depart', TextType::class, [
                'label' => 'Ville de départ',
                'attr' => [
                    'placeholder' => 'Ex: Paris'
                ]
            ])
            ->add('arrivee', TextType::class, [
                'label' => 'Ville d\'arrivée',
                'attr' => [
                    'placeholder' => 'Ex: Lyon'
                ]
            ])
            ->add('date', DateType::class, [
                'label' => 'Date de départ',
                'widget' => 'single_text',
                'html5' => true,
                'data' => new \DateTime(),
                'attr' => [
                    'min' => (new \DateTime())->format('Y-m-d')
                ]
            ])
            ->add('heureDepart', TimeType::class, [
                'label' => 'Heure de départ',
                'widget' => 'single_text',
                'html5' => true,
            ])
            ->add('heureArrivee', TimeType::class, [
                'label' => 'Heure d\'arrivée estimée',
                'widget' => 'single_text',
                'html5' => true,
                'required' => false,
            ])
            ->add('places', IntegerType::class, [
                'label' => 'Nombre de places disponibles',
                'attr' => [
                    'min' => 1,
                    'max' => 8
                ]
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix par passager (en crédits)',
                'currency' => false,
                'scale' => 2,
                'attr' => [
                    'min' => 0
                ],
                'help' => 'Note : 2 crédits seront facturés par la plateforme pour chaque passager'
            ])
            ->add('vehicule', EntityType::class, [
                'class' => Vehicule::class,
                'label' => 'Véhicule',
                'choice_label' => function(Vehicule $vehicule) {
                    return $vehicule->getMarque() . ' ' . $vehicule->getModele() . 
                           ' (' . $vehicule->getImmatriculation() . ')' .
                           ($vehicule->isEstEcologique() ? ' - Écologique' : '');
                },
                'placeholder' => 'Choisissez un véhicule',
                'query_builder' => function ($repository) use ($user) {
                    return $repository->createQueryBuilder('v')
                        ->where('v.proprietaire = :user')
                        ->setParameter('user', $user)
                        ->orderBy('v.marque', 'ASC');
                },
                'required' => true,
                'help' => 'Sélectionnez le véhicule que vous utiliserez pour ce trajet'
            ])
            ->add('estEcologique', CheckboxType::class, [
                'label' => 'Ce trajet est écologique',
                'required' => false,
                'help' => 'Cochez cette case si vous utilisez un véhicule électrique ou si vous pratiquez l\'éco-conduite'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Publier le trajet',
                'attr' => [
                    'class' => 'btn btn-success w-100'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trajet::class,
        ]);
    }
}