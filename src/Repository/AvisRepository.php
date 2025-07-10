<?php

namespace App\Repository;

use App\Entity\Avis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Avis>
 *
 * @method Avis|null find($id, $lockMode = null, $lockVersion = null)
 * @method Avis|null findOneBy(array $criteria, array $orderBy = null)
 * @method Avis[]    findAll()
 * @method Avis[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    /**
     * Trouve les avis validés d'un chauffeur
     */
    public function findAvisValidesChauffeur(int $chauffeurId): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.passager', 'p')
            ->where('a.chauffeur = :chauffeurId')
            ->andWhere('a.valide = true')
            ->setParameter('chauffeurId', $chauffeurId)
            ->orderBy('a.id', 'DESC')
            ->setMaxResults(10) // Limiter à 10 avis récents
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre d'avis par note pour un chauffeur
     */
    public function getStatistiquesNotes(int $chauffeurId): array
    {
        $result = $this->createQueryBuilder('a')
            ->select('a.note, COUNT(a.id) as nombre')
            ->where('a.chauffeur = :chauffeurId')
            ->andWhere('a.valide = true')
            ->setParameter('chauffeurId', $chauffeurId)
            ->groupBy('a.note')
            ->orderBy('a.note', 'DESC')
            ->getQuery()
            ->getResult();

        // Convertir en tableau associatif
        $stats = [];
        for ($i = 5; $i >= 1; $i--) {
            $stats[$i] = 0;
        }
        
        foreach ($result as $row) {
            $stats[$row['note']] = (int)$row['nombre'];
        }

        return $stats;
    }
}