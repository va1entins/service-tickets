<?php

declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Api\Provider\TechnicianPerformanceProvider;

/**
 * Zasób API dla raportu wydajności techników
 */
#[ApiResource(
    uriTemplate: '/reports/technicians-performance',
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['technician_performance:read']],
        ),
    ],
    security: "is_granted('ROLE_ADMIN')",
    provider: TechnicianPerformanceProvider::class
)]
class TechnicianPerformance {}
