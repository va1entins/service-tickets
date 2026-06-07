<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\TicketClosedMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handler powiadomienia e-mail po zamknięciu zgłoszenia
 */
#[AsMessageHandler]
final class TicketClosedMessageHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(TicketClosedMessage $message): void
    {
        $this->logger->info('Sending email notification for closed ticket.', [
            'ticketId'        => $message->ticketId,
            'ticketTitle'     => $message->ticketTitle,
            'technicianEmail' => $message->technicianEmail,
        ]);
    }
}
