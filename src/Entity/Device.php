<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\DeviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Encja urządzenia serwisowanego
 */
#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ORM\Table(name: 'devices')]
#[UniqueEntity(fields: ['serialNumber'], message: 'Urządzenie z tym numerem seryjnym już istnieje.')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['device:read']],
    denormalizationContext: ['groups' => ['device:write']]
)]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['device:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Numer seryjny jest wymagany.')]
    #[Assert\Length(max: 100, maxMessage: 'Numer seryjny nie może być dłuższy niż {{ limit }} znaków.')]
    #[Groups(['device:read', 'device:write', 'ticket:read'])]
    private string $serialNumber;

    #[ORM\Column(type: 'string', length: 150)]
    #[Assert\NotBlank(message: 'Model jest wymagany.')]
    #[Assert\Length(max: 150, maxMessage: 'Model nie może być dłuższy niż {{ limit }} znaków.')]
    #[Groups(['device:read', 'device:write', 'ticket:read'])]
    private string $model;

    #[ORM\Column(type: 'string', length: 200)]
    #[Assert\NotBlank(message: 'Nazwa klienta jest wymagana.')]
    #[Assert\Length(max: 200, maxMessage: 'Nazwa klienta nie może być dłuższa niż {{ limit }} znaków.')]
    #[Groups(['device:read', 'device:write', 'ticket:read'])]
    private string $customerName;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['device:read'])]
    private \DateTimeImmutable $createdAt;

    /** @var Collection<int, Ticket> */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'device')]
    private Collection $tickets;

    public function __construct()
    {
        $this->tickets   = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): self
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return Collection<int, Ticket> */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }
}
