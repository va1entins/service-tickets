<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\DTO\TechnicianPerformanceOutput;
use App\Repository\TechnicianRepository;

/**
 * Provider dla raportu wydajności techników.
 * Deleguje zapytanie SQL do repozytorium — bez N+1.
 *
 * @implements ProviderInterface<TechnicianPerformanceOutput>
 */
final class TechnicianPerformanceProvider implements ProviderInterface
{
    public function __construct(
        private readonly TechnicianRepository $technicianRepository,
    ) {}

    /**
     * @return TechnicianPerformanceOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $rows = $this->technicianRepository->findPerformanceStats();

        return array_map(static function (array $row): TechnicianPerformanceOutput {
            $dto = new TechnicianPerformanceOutput();
            $dto->technicianId            = (int) $row['technician_id'];
            $dto->name                    = $row['name'];
            $dto->closedTickets           = (int) $row['closed_tickets'];
            $dto->averageClosingTimeHours = round((float) $row['avg_hours'], 2);

            return $dto;
        }, $rows);
    }
}
