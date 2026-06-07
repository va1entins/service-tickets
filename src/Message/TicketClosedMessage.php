<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Wiadomość wysyłana po zamknięciu zgłoszenia
 */
final class TicketClosedMessage
{
    public function __construct(
        public readonly int $ticketId,
        public readonly string $technicianEmail,
        public readonly string $ticketTitle,
    ) {}
}
