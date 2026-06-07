<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\TicketHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repozytorium historii zmian zgłoszeń
 *
 * @extends ServiceEntityRepository<TicketHistory>
 */
class TicketHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TicketHistory::class);
    }

    /** @return TicketHistory[] */
    public function findByTicketId(int $ticketId): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.ticket = :ticketId')
            ->setParameter('ticketId', $ticketId)
            ->orderBy('h.changedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
