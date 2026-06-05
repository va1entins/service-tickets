<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Technician;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
}
