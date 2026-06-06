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
        // Pobranie zgłoszenia po ID z URI
        $ticketId = $uriVariables['id'] ?? null;
        $ticket   = $this->ticketRepository->find($ticketId);

        if (!$ticket instanceof Ticket) {
            throw new NotFoundHttpException(sprintf('Zgłoszenie o ID %d nie istnieje.', $ticketId));
        }

        // Walidacja przejścia statusu — tylko NEW może przejść do ASSIGNED
        if (!$ticket->getStatus()->canTransitionTo(TicketStatus::ASSIGNED)) {
            throw new ConflictHttpException(sprintf(
                'Nie można przypisać technika — zgłoszenie ma status "%s". Wymagany status: NEW.',
                $ticket->getStatus()->label()
            ));
        }

        // Pobranie technika
        $technician = $this->technicianRepository->find($data->technicianId);

        if ($technician === null) {
            throw new NotFoundHttpException(sprintf('Technik o ID %d nie istnieje.', $data->technicianId));
        }

        // Walidacja czy technik jest aktywny
        if (!$technician->isActive()) {
            throw new UnprocessableEntityHttpException(sprintf(
                'Technik "%s" jest nieaktywny i nie może zostać przypisany.',
                $technician->getFullName()
            ));
        }

        // Ustalenie autora zmiany
        $user      = $this->security->getUser();
        $changedBy = $user?->getUserIdentifier() ?? 'system';

        // Zapis historii zmiany statusu
        $history = new TicketHistory(
            ticket:    $ticket,
            oldStatus: $ticket->getStatus(),
            newStatus: TicketStatus::ASSIGNED,
            changedBy: $changedBy,
        );

        // Aktualizacja zgłoszenia
        $ticket->setAssignedTechnician($technician);
        $ticket->setStatus(TicketStatus::ASSIGNED);
        $ticket->addHistory($history);

        $this->entityManager->flush();

        return $ticket;
    }
}
