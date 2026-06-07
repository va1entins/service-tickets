<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Ticket;
use App\Enum\TicketPriority;
use App\Enum\TicketStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repozytorium zgłoszeń serwisowych
 *
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    /**
     * Buduje query builder dla listy ticketów z filtrami i sortowaniem
     *
     * @param array{
     *     status?: TicketStatus,
     *     priority?: TicketPriority,
     *     serialNumber?: string,
     *     sortBy?: string,
     *     sortDir?: string
     * } $filters
     */
    public function createFilteredQueryBuilder(array $filters): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.assignedTechnician', 'tech')
            ->leftJoin('t.device', 'd')
            ->addSelect('tech', 'd');

        if (isset($filters['status'])) {
            $qb->andWhere('t.status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $qb->andWhere('t.priority = :priority')
               ->setParameter('priority', $filters['priority']);
        }

        if (isset($filters['serialNumber'])) {
            $qb->andWhere('d.serialNumber LIKE :serial')
               ->setParameter('serial', '%' . $filters['serialNumber'] . '%');
        }

        $allowedSortFields = ['t.createdAt', 't.updatedAt', 't.priority', 't.status'];
        $sortBy  = $filters['sortBy']  ?? 't.createdAt';
        $sortDir = strtoupper($filters['sortDir'] ?? 'DESC');

        if (!in_array($sortBy, $allowedSortFields, true)) {
            $sortBy = 't.createdAt';
        }

        if (!in_array($sortDir, ['ASC', 'DESC'], true)) {
            $sortDir = 'DESC';
        }

        $qb->orderBy($sortBy, $sortDir);

        return $qb;
    }
}
