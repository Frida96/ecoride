<?php

namespace App\Repository;

use App\Entity\Vehicule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

// Ensure the Vehicule entity exists at src/Entity/Vehicule.php

/**
 * @extends ServiceEntityRepository<Vehicule>
 *
 * @method Vehicule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vehicule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vehicule[]    findAll()
 * @method Vehicule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VehiculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicule::class);
    }

    /**
     * Trouve tous les véhicules écologiques
     *
     * @return Vehicule[]
     */
    public function findAllEcological(): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.estEcologique = :eco')
            ->setParameter('eco', true)
            ->orderBy('v.marque', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les véhicules d'un utilisateur
     *
     * @param int $userId
     * @return Vehicule[]
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.proprietaire = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('v.marque', 'ASC')
            ->getQuery()
            ->getResult();
    }

}