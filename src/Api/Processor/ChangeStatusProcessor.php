<?php

declare(strict_types=1);

namespace App\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\DTO\ChangeStatusInput;
use App\Entity\Ticket;
use App\Entity\TicketHistory;
use App\Enum\TicketStatus;
use App\Message\TicketClosedMessage;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Procesor zmiany statusu zgłoszenia z walidacją przejść
 */
final class ChangeStatusProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TicketRepository       $ticketRepository,
        private readonly EntityManagerInterface $em,
        private readonly Security               $security,
        private readonly MessageBusInterface    $bus,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Ticket
    {
        /** @var ChangeStatusInput $data */

        $ticket = $this->ticketRepository->find($uriVariables['id'] ?? null);

        if (!$ticket instanceof Ticket) {
            throw new NotFoundHttpException('Zgłoszenie nie zostało znalezione.');
        }

        $newStatus = TicketStatus::tryFrom($data->status);

        if ($newStatus === null) {
            throw new UnprocessableEntityHttpException(
                sprintf('Nieprawidłowy status: "%s".', $data->status)
            );
        }

        if (!$ticket->getStatus()->canTransitionTo($newStatus)) {
            throw new ConflictHttpException(sprintf(
                'Przejście ze statusu "%s" do "%s" jest niedozwolone.',
                $ticket->getStatus()->value,
                $newStatus->value
            ));
        }

        $oldStatus = $ticket->getStatus();
        $ticket->setStatus($newStatus);

        if ($newStatus->isFinal()) {
            $ticket->setClosedAt(new \DateTimeImmutable());
        }

        $changedBy = $this->security->getUser()?->getUserIdentifier() ?? 'system';
        $history   = new TicketHistory($ticket, $oldStatus, $newStatus, $changedBy);
        $ticket->addHistory($history);

        $this->em->flush();

        // Wysyłka wiadomości do Messengera po zamknięciu zgłoszenia
        if ($newStatus->isFinal()) {
            $email = $ticket->getAssignedTechnician()?->getEmail() ?? 'unknown@proassist.pl';

            $this->bus->dispatch(new TicketClosedMessage(
                ticketId:        $ticket->getId(),
                technicianEmail: $email,
                ticketTitle:     $ticket->getTitle(),
            ));
        }

        return $ticket;
    }
}
