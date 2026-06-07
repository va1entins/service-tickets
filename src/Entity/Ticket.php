<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use App\Api\DTO\AssignTechnicianInput;
use App\Api\DTO\ChangeStatusInput;
use App\Api\Processor\AssignTechnicianProcessor;
use App\Api\Processor\ChangeStatusProcessor;
use App\Api\Provider\TicketCollectionProvider;
use App\Enum\TicketPriority;
use App\Enum\TicketStatus;
use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Encja zgłoszenia serwisowego
 */
#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Table(name: 'tickets')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            provider: TicketCollectionProvider::class
        ),
        new Get(),
        new Post(
            security: "is_granted('ROLE_ADMIN')"
        ),
        new Post(
            uriTemplate: '/tickets/{id}/assign',
            uriVariables: [
                'id' => new Link(fromClass: Ticket::class, identifiers: ['id']),
            ],
            openapi: new Operation(
                summary: 'Przypisuje technika do zgłoszenia',
                description: 'Przypisuje aktywnego technika do zgłoszenia o statusie NEW. Zmienia status na ASSIGNED i zapisuje historię.',
            ),
            security: "is_granted('ROLE_ADMIN')",
            input: AssignTechnicianInput::class,
            name: 'ticket_assign',
            processor: AssignTechnicianProcessor::class,
        ),
        new Post(
            uriTemplate: '/tickets/{id}/status',
            uriVariables: [
                'id' => new Link(fromClass: Ticket::class, identifiers: ['id']),
            ],
            openapi: new Operation(
                summary: 'Zmienia status zgłoszenia',
                description: 'Waliduje przejście statusu zgodnie z dozwolonym workflowem i zapisuje historię zmian.',
            ),
            security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_TECHNICIAN')",
            input: ChangeStatusInput::class,
            name: 'ticket_change_status',
            processor: ChangeStatusProcessor::class,
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN') or (is_granted('ROLE_TECHNICIAN') and object.getAssignedTechnician() != null and object.getAssignedTechnician().getId() == user.getId())"
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN') or (is_granted('ROLE_TECHNICIAN') and object.getAssignedTechnician() != null and object.getAssignedTechnician().getId() == user.getId())"
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')"
        ),
    ],
    normalizationContext: ['groups' => ['ticket:read']],
    denormalizationContext: ['groups' => ['ticket:write']]
)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['ticket:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Tytuł zgłoszenia jest wymagany.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Tytuł musi mieć co najmniej {{ limit }} znaki.',
        maxMessage: 'Tytuł nie może być dłuższy niż {{ limit }} znaków.'
    )]
    #[Groups(['ticket:read', 'ticket:write'])]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 5000, maxMessage: 'Opis nie może być dłuższy niż {{ limit }} znaków.')]
    #[Groups(['ticket:read', 'ticket:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'string', enumType: TicketPriority::class)]
    #[Assert\NotNull(message: 'Priorytet jest wymagany.')]
    #[Groups(['ticket:read', 'ticket:write'])]
    private TicketPriority $priority;

    #[ORM\Column(type: 'string', enumType: TicketStatus::class)]
    #[Groups(['ticket:read'])]
    private TicketStatus $status;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['ticket:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['ticket:read'])]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['ticket:read'])]
    private ?\DateTimeImmutable $closedAt = null;

    #[ORM\ManyToOne(targetEntity: Technician::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['ticket:read'])]
    private ?Technician $assignedTechnician = null;

    #[ORM\ManyToOne(targetEntity: Device::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'Urządzenie jest wymagane.')]
    #[Groups(['ticket:read', 'ticket:write'])]
    private Device $device;

    /** @var Collection<int, TicketHistory> */
    #[ORM\OneToMany(targetEntity: TicketHistory::class, mappedBy: 'ticket', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['changedAt' => 'ASC'])]
    #[Groups(['ticket:read'])]
    private Collection $history;

    public function __construct()
    {
        $this->status    = TicketStatus::NEW;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->history   = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPriority(): TicketPriority
    {
        return $this->priority;
    }

    public function setPriority(TicketPriority $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getStatus(): TicketStatus
    {
        return $this->status;
    }

    public function setStatus(TicketStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeImmutable $closedAt): self
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    public function getAssignedTechnician(): ?Technician
    {
        return $this->assignedTechnician;
    }

    public function setAssignedTechnician(?Technician $technician): self
    {
        $this->assignedTechnician = $technician;

        return $this;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function setDevice(Device $device): self
    {
        $this->device = $device;

        return $this;
    }

    /** @return Collection<int, TicketHistory> */
    public function getHistory(): Collection
    {
        return $this->history;
    }

    public function addHistory(TicketHistory $history): self
    {
        if (!$this->history->contains($history)) {
            $this->history->add($history);
            $history->setTicket($this);
        }

        return $this;
    }
}
