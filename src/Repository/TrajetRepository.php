<?php

namespace App\Repository;

use App\Entity\Trajet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trajet>
 *
 * @method Trajet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trajet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trajet[]    findAll()
 * @method Trajet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrajetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trajet::class);
    }

    /**
     * Trouve les trajets disponibles selon les critères de recherche et filtres
     */
    public function findTrajetsDisponibles(string $depart, string $arrivee, \DateTime $date, array $filtres = []): array
    {
        $dateDebut = clone $date;
        $dateDebut->setTime(0, 0, 0);
        
        $dateFin = clone $date;
        $dateFin->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.chauffeur', 'c')
            ->leftJoin('t.vehicule', 'v')
            ->leftJoin('t.participations', 'p')
            ->where('LOWER(t.lieuDepart) LIKE LOWER(:depart)')
            ->andWhere('LOWER(t.lieuArrivee) LIKE LOWER(:arrivee)')
            ->andWhere('t.dateDepart BETWEEN :dateDebut AND :dateFin')
            ->andWhere('t.statut = :statut')
            ->setParameter('depart', '%' . $depart . '%')
            ->setParameter('arrivee', '%' . $arrivee . '%')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->setParameter('statut', 'en_attente');

        // Filtre écologique
        if (!empty($filtres['ecologique'])) {
            $qb->andWhere('LOWER(v.energie) = :energie')
               ->setParameter('energie', 'electrique');
        }

        // Filtre prix maximum
        if (!empty($filtres['prix_max']) && is_numeric($filtres['prix_max'])) {
            $qb->andWhere('t.prix <= :prixMax')
               ->setParameter('prixMax', (int)$filtres['prix_max']);
        }

        // Filtre durée maximum (en heures)
        if (!empty($filtres['duree_max']) && is_numeric($filtres['duree_max'])) {
            $qb->andWhere('TIMESTAMPDIFF(HOUR, t.dateDepart, t.dateArrivee) <= :dureeMax')
               ->setParameter('dureeMax', (int)$filtres['duree_max']);
        }

        return $qb->groupBy('t.id')
            ->having('t.nbPlaces > COUNT(p.id)')
            ->orderBy('t.dateDepart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les trajets disponibles avec filtre sur la note du chauffeur
     */
    public function findTrajetsAvecNoteMinimale(array $trajets, float $noteMin): array
    {
        if (empty($trajets) || $noteMin <= 0) {
            return $trajets;
        }

        $trajetsFiltrés = [];
        foreach ($trajets as $trajet) {
            $noteChauffeur = $this->getNoteMoyenneChauffeur($trajet->getChauffeur()->getId());
            if ($noteChauffeur >= $noteMin) {
                $trajetsFiltrés[] = $trajet;
            }
        }

        return $trajetsFiltrés;
    }

    /**
     * Trouve le prochain trajet disponible après une date donnée
     */
    public function findProchainTrajetDisponible(string $depart, string $arrivee, \DateTime $dateApres): ?Trajet
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.participations', 'p')
            ->where('LOWER(t.lieuDepart) LIKE LOWER(:depart)')
            ->andWhere('LOWER(t.lieuArrivee) LIKE LOWER(:arrivee)')
            ->andWhere('t.dateDepart > :dateApres')
            ->andWhere('t.statut = :statut')
            ->groupBy('t.id')
            ->having('t.nbPlaces > COUNT(p.id)')
            ->setParameter('depart', '%' . $depart . '%')
            ->setParameter('arrivee', '%' . $arrivee . '%')
            ->setParameter('dateApres', $dateApres)
            ->setParameter('statut', 'en_attente')
            ->orderBy('t.dateDepart', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Calcule la note moyenne d'un chauffeur
     */
    public function getNoteMoyenneChauffeur(int $chauffeurId): float
    {
        $result = $this->getEntityManager()
            ->createQuery('
                SELECT AVG(a.note) as moyenne
                FROM App\Entity\Avis a 
                WHERE a.chauffeur = :chauffeurId 
                AND a.valide = true
            ')
            ->setParameter('chauffeurId', $chauffeurId)
            ->getSingleScalarResult();

        return $result ? round((float)$result, 1) : 0.0;
    }
}