<?php

// src/Repository/TrajetRepository.php
namespace App\Repository;

use App\Entity\Trajet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TrajetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trajet::class);
    }

    /**
     * Trouve les trajets disponibles selon les critères
     */
    public function trouverTrajetsDisponibles(string $depart, string $arrivee, \DateTime $date): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.depart = :depart')
            ->andWhere('t.arrivee = :arrivee')
            ->andWhere('t.date = :date')
            ->andWhere('t.places > 0')
            ->setParameter('depart', $depart)
            ->setParameter('arrivee', $arrivee)
            ->setParameter('date', $date->format('Y-m-d'))
            ->orderBy('t.heureDepart', 'ASC');
            
        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve la date du prochain trajet disponible
     */
    public function trouverDateProchainTrajet(string $depart, string $arrivee, \DateTime $date): ?\DateTime
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t.date')
            ->where('t.depart = :depart')
            ->andWhere('t.arrivee = :arrivee')
            ->andWhere('t.date > :date')
            ->andWhere('t.places > 0')
            ->setParameter('depart', $depart)
            ->setParameter('arrivee', $arrivee)
            ->setParameter('date', $date->format('Y-m-d'))
            ->orderBy('t.date', 'ASC')
            ->setMaxResults(1);
            
        $result = $qb->getQuery()->getOneOrNullResult();
        
        return $result ? $result['date'] : null;
    }
}