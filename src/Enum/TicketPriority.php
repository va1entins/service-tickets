<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Priorytety zgłoszeń serwisowych
 */
enum TicketPriority: string
{
    case LOW      = 'LOW';
    case MEDIUM   = 'MEDIUM';
    case HIGH     = 'HIGH';
    case CRITICAL = 'CRITICAL';

    public function label(): string
    {
        return match($this) {
            self::LOW      => 'Niski',
            self::MEDIUM   => 'Średni',
            self::HIGH     => 'Wysoki',
            self::CRITICAL => 'Krytyczny',
        };
    }
}
