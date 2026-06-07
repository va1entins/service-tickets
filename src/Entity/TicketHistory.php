<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Enum\TicketStatus;
use App\Repository\TicketHistoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Historia zmian statusów zgłoszenia — tworzona automatycznie
 */
#[ORM\Entity(repositoryClass: TicketHistoryRepository::class)]
#[ORM\Table(name: 'ticket_history')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['ticket_history:read']]
)]
class TicketHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['ticket_history:read', 'ticket:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'history')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['ticket_history:read'])]
    private Ticket $ticket;

    #[ORM\Column(type: 'string', nullable: true, enumType: TicketStatus::class)]
    #[Groups(['ticket_history:read', 'ticket:read'])]
    private ?TicketStatus $oldStatus;

    #[ORM\Column(type: 'string', enumType: TicketStatus::class)]
    #[Groups(['ticket_history:read', 'ticket:read'])]
    private TicketStatus $newStatus;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['ticket_history:read', 'ticket:read'])]
    private \DateTimeImmutable $changedAt;

    /** Identyfikator użytkownika lub systemu, który dokonał zmiany */
    #[ORM\Column(type: 'string', length: 200)]
    #[Groups(['ticket_history:read', 'ticket:read'])]
    private string $changedBy;

    public function __construct(
        Ticket        $ticket,
        ?TicketStatus $oldStatus,
        TicketStatus  $newStatus,
        string        $changedBy,
    ) {
        $this->ticket    = $ticket;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy;
        $this->changedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    public function setTicket(Ticket $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getOldStatus(): ?TicketStatus
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): TicketStatus
    {
        return $this->newStatus;
    }

    public function getChangedAt(): \DateTimeImmutable
    {
        return $this->changedAt;
    }

    public function getChangedBy(): string
    {
        return $this->changedBy;
    }
}
