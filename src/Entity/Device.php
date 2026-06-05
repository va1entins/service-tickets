<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DeviceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Encja urządzenia serwisowanego
 */
#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ORM\Table(name: 'devices')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['serialNumber'], message: 'Urządzenie z tym numerem seryjnym już istnieje.')]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    #[Assert\NotBlank(message: 'Numer seryjny jest wymagany.')]
    #[Assert\Length(max: 100, maxMessage: 'Numer seryjny nie może być dłuższy niż {{ limit }} znaków.')]
    private string $serialNumber;

    #[ORM\Column(type: 'string', length: 150)]
    #[Assert\NotBlank(message: 'Model jest wymagany.')]
    #[Assert\Length(max: 150, maxMessage: 'Model nie może być dłuższy niż {{ limit }} znaków.')]
    private string $model;

    #[ORM\Column(type: 'string', length: 200)]
    #[Assert\NotBlank(message: 'Nazwa klienta jest wymagana.')]
    #[Assert\Length(max: 200, maxMessage: 'Nazwa klienta nie może być dłuższa niż {{ limit }} znaków.')]
    private string $customerName;

    #[ORM\Column(type: 'datetime_immutable')]
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
