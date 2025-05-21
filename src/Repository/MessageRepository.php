<?php
namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Compte le nombre de messages non lus
     */
    public function countUnread(): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.lu = :lu')
            ->setParameter('lu', false)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les messages avec pagination
     */
    public function findPaginated(int $page = 1, int $limit = 10): array
    {
        $query = $this->createQueryBuilder('m')
            ->orderBy('m.dateEnvoi', 'DESC')
            ->getQuery();

        $offset = ($page - 1) * $limit;
        $query->setFirstResult($offset);
        $query->setMaxResults($limit);

        return [
            'results' => $query->getResult(),
            'totalItems' => $this->count([]),
            'totalPages' => ceil($this->count([]) / $limit),
            'page' => $page,
            'limit' => $limit,
        ];
    }
}