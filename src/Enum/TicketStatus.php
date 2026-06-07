<?php

declare(strict_types=1);

namespace App\Enum;

/**
 * Statusy zgłoszeń serwisowych z dozwolonymi przejściami
 */
enum TicketStatus: string
{
    case NEW         = 'NEW';
    case ASSIGNED    = 'ASSIGNED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case DONE        = 'DONE';
    case CANCELLED   = 'CANCELLED';

    /**
     * Zwraca dozwolone statusy docelowe dla danego statusu
     *
     * @return TicketStatus[]
     */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::NEW         => [self::ASSIGNED],
            self::ASSIGNED    => [self::IN_PROGRESS],
            self::IN_PROGRESS => [self::DONE, self::CANCELLED],
            self::DONE        => [],
            self::CANCELLED   => [],
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions(), true);
    }

    public function isFinal(): bool
    {
        return match($this) {
            self::DONE, self::CANCELLED => true,
            default                     => false,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::NEW         => 'Nowe',
            self::ASSIGNED    => 'Przypisane',
            self::IN_PROGRESS => 'W trakcie',
            self::DONE        => 'Zakończone',
            self::CANCELLED   => 'Anulowane',
        };
    }
}
