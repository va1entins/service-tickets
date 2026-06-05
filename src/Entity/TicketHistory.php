<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TicketStatus;
use App\Repository\TicketHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Historia zmian statusów zgłoszenia — tworzona automatycznie
 */
#[ORM\Entity(repositoryClass: TicketHistoryRepository::class)]
#[ORM\Table(name: 'ticket_history')]
class TicketHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'history')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Ticket $ticket;

    #[ORM\Column(type: 'string', nullable: true, enumType: TicketStatus::class)]
    private ?TicketStatus $oldStatus;

    #[ORM\Column(type: 'string', enumType: TicketStatus::class)]
    private TicketStatus $newStatus;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $changedAt;

    /** Identyfikator użytkownika lub systemu, który dokonał zmiany */
    #[ORM\Column(type: 'string', length: 200)]
    private string $changedBy;

    public function __construct(
        Ticket        $ticket,
        ?TicketStatus $oldStatus,
        TicketStatus  $newStatus,
        string        $changedBy,
    ) {
        $this->ticket     = $ticket;
        $this->oldStatus  = $oldStatus;
        $this->newStatus  = $newStatus;
        $this->changedBy  = $changedBy;
        $this->changedAt  = new \DateTimeImmutable();
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
