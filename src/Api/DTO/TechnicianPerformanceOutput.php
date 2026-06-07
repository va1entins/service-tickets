<?php

declare(strict_types=1);

namespace App\Api\DTO;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * DTO z danymi wydajności technika
 */
final class TechnicianPerformanceOutput
{
    #[Groups(['technician_performance:read'])]
    public int $technicianId;

    #[Groups(['technician_performance:read'])]
    public string $name;

    #[Groups(['technician_performance:read'])]
    public int $closedTickets;

    #[Groups(['technician_performance:read'])]
    public float $averageClosingTimeHours;
}
