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
use App\Repository\TechnicianRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Encja technika serwisowego
 */
#[ORM\Entity(repositoryClass: TechnicianRepository::class)]
#[ORM\Table(name: 'technicians')]
#[UniqueEntity(fields: ['email'], message: 'Technik z tym adresem e-mail już istnieje.')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_ADMIN')"),
        new Put(security: "is_granted('ROLE_ADMIN')"),
        new Patch(security: "is_granted('ROLE_ADMIN')"),
        new Delete(security: "is_granted('ROLE_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['technician:read']],
    denormalizationContext: ['groups' => ['technician:write']]
)]
class Technician
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['technician:read', 'ticket:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Imię jest wymagane.')]
    #[Assert\Length(max: 100, maxMessage: 'Imię nie może być dłuższe niż {{ limit }} znaków.')]
    #[Groups(['technician:read', 'technician:write', 'ticket:read'])]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Nazwisko jest wymagane.')]
    #[Assert\Length(max: 100, maxMessage: 'Nazwisko nie może być dłuższe niż {{ limit }} znaków.')]
    #[Groups(['technician:read', 'technician:write', 'ticket:read'])]
    private string $lastName;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Adres e-mail jest wymagany.')]
    #[Assert\Email(message: 'Podany adres e-mail jest nieprawidłowy.')]
    #[Assert\Length(max: 180, maxMessage: 'Adres e-mail nie może być dłuższy niż {{ limit }} znaków.')]
    #[Groups(['technician:read', 'technician:write'])]
    private string $email;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(['technician:read', 'technician:write'])]
    private bool $active = true;

    /** @var Collection<int, Ticket> */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'assignedTechnician')]
    private Collection $tickets;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /** @return Collection<int, Ticket> */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }
}
