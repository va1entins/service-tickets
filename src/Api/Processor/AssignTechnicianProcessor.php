<?php

declare(strict_types=1);

namespace App\Api\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Api\DTO\AssignTechnicianInput;
use App\Entity\Ticket;
use App\Entity\TicketHistory;
use App\Enum\TicketStatus;
use App\Repository\TechnicianRepository;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Processor obsługujący przypisanie technika do zgłoszenia
 *
 * @implements ProcessorInterface<AssignTechnicianInput, Ticket>
 */
final class AssignTechnicianProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly TicketRepository     $ticketRepository,
        private readonly TechnicianRepository $technicianRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security             $security,
    ) {}

    /**
     * @param AssignTechnicianInput $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Ticket
    {
        $ticketId = $uriVariables['id'] ?? null;
        $ticket   = $this->ticketRepository->find($ticketId);

        if (!$ticket instanceof Ticket) {
            throw new NotFoundHttpException(sprintf('Zgłoszenie o ID %d nie istnieje.', $ticketId));
        }

        if (!$ticket->getStatus()->canTransitionTo(TicketStatus::ASSIGNED)) {
            throw new ConflictHttpException(sprintf(
                'Nie można przypisać technika — zgłoszenie ma status "%s". Wymagany status: NEW.',
                $ticket->getStatus()->label()
            ));
        }

        $technician = $this->technicianRepository->find($data->technicianId);

        if ($technician === null) {
            throw new NotFoundHttpException(sprintf('Technik o ID %d nie istnieje.', $data->technicianId));
        }

        if (!$technician->isActive()) {
            throw new UnprocessableEntityHttpException(sprintf(
                'Technik "%s" jest nieaktywny i nie może zostać przypisany.',
                $technician->getFullName()
            ));
        }

        $user      = $this->security->getUser();
        $changedBy = $user?->getUserIdentifier() ?? 'system';

        $history = new TicketHistory(
            ticket:    $ticket,
            oldStatus: $ticket->getStatus(),
            newStatus: TicketStatus::ASSIGNED,
            changedBy: $changedBy,
        );

        $ticket->setAssignedTechnician($technician);
        $ticket->setStatus(TicketStatus::ASSIGNED);
        $ticket->addHistory($history);

        $this->entityManager->flush();

        return $ticket;
    }
}
