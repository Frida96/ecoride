<?php
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
    /**
     * Compte le nombre de trajets créés par jour.
     */
    public function countTrajetsByDay(\DateTime $dateDebut, \DateTime $dateFin): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT DATE(t.date) AS jour, COUNT(t.id) AS total
            FROM trajet t
            WHERE t.date BETWEEN :debut AND :fin
            GROUP BY jour
            ORDER BY jour ASC
        ';

        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery([
            'debut' => $dateDebut->format('Y-m-d'),
            'fin' => $dateFin->format('Y-m-d'),
        ])->fetchAllAssociative();

        return $this->fillMissingDays($results, $dateDebut, $dateFin, 'total', 'count');
    }

    /**
     * Calcule la somme des crédits gagnés par jour (2 crédits par trajet).
     */
    public function sumCreditsByDay(\DateTime $dateDebut, \DateTime $dateFin): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT DATE(t.date) AS jour, COUNT(t.id) * 2 AS credits
            FROM trajet t
            WHERE t.date BETWEEN :debut AND :fin
            GROUP BY jour
            ORDER BY jour ASC
        ';

        $stmt = $conn->prepare($sql);
        $results = $stmt->executeQuery([
            'debut' => $dateDebut->format('Y-m-d'),
            'fin' => $dateFin->format('Y-m-d'),
        ])->fetchAllAssociative();

        return $this->fillMissingDays($results, $dateDebut, $dateFin, 'credits', 'credits');
    }

    /**
     * Complète les jours manquants dans une période, en insérant 0 si aucune donnée.
     */
    private function fillMissingDays(array $results, \DateTime $dateDebut, \DateTime $dateFin, string $keyInResult, string $keyOut): array
    {
        $formattedResults = [];
        $currentDate = (clone $dateDebut)->setTime(0, 0);

        $datesMap = [];
        foreach ($results as $result) {
            $datesMap[$result['jour']] = (int) $result[$keyInResult];
        }

        while ($currentDate <= $dateFin) {
            $dateStr = $currentDate->format('Y-m-d');
            $formattedResults[] = [
                'date' => clone $currentDate,
                $keyOut => $datesMap[$dateStr] ?? 0,
            ];
            $currentDate->modify('+1 day');
        }

        return $formattedResults;
    }
}