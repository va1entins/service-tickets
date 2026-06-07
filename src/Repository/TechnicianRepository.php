<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Technician;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Repozytorium techników serwisowych
 *
 * @extends ServiceEntityRepository<Technician>
 */
class TechnicianRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Technician::class);
    }

    /** @return Technician[] */
    public function findActive(): array
    {
        return $this->findBy(['active' => true]);
    }

    public function findActiveById(int $id): ?Technician
    {
        return $this->findOneBy(['id' => $id, 'active' => true]);
    }

    /**
     * Zwraca statystyki wydajności techników — jedno zapytanie SQL bez N+1
     *
     * @return array<int, array{technician_id: int, name: string, closed_tickets: int, avg_hours: float}>
     */
    public function findPerformanceStats(): array
    {
        $sql = '
            SELECT
                t.id                                                        AS technician_id,
                CONCAT(t.first_name, \' \', t.last_name)                   AS name,
                COUNT(tk.id)                                                AS closed_tickets,
                COALESCE(
                    AVG(EXTRACT(EPOCH FROM (tk.closed_at - tk.created_at)) / 3600.0),
                    0
                )                                                           AS avg_hours
            FROM technicians t
            LEFT JOIN tickets tk
                ON tk.assigned_technician_id = t.id
                AND tk.status = :status
                AND tk.closed_at IS NOT NULL
            GROUP BY t.id, t.first_name, t.last_name
            ORDER BY closed_tickets DESC
        ';

        try {
            return $this->getEntityManager()
                ->getConnection()
                ->executeQuery($sql, ['status' => 'DONE'])
                ->fetchAllAssociative();
        } catch (DbalException $e) {
            throw new ServiceUnavailableHttpException(
                message: 'Błąd podczas pobierania statystyk wydajności techników.',
                previous: $e
            );
        }
    }
}
