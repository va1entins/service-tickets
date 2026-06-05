<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TechnicianRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Encja technika serwisowego
 */
#[ORM\Entity(repositoryClass: TechnicianRepository::class)]
#[ORM\Table(name: 'technicians')]
#[UniqueEntity(fields: ['email'], message: 'Technik z tym adresem e-mail już istnieje.')]
class Technician
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Imię jest wymagane.')]
    #[Assert\Length(max: 100, maxMessage: 'Imię nie może być dłuższe niż {{ limit }} znaków.')]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Nazwisko jest wymagane.')]
    #[Assert\Length(max: 100, maxMessage: 'Nazwisko nie może być dłuższe niż {{ limit }} znaków.')]
    private string $lastName;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Adres e-mail jest wymagany.')]
    #[Assert\Email(message: 'Podany adres e-mail jest nieprawidłowy.')]
    #[Assert\Length(max: 180, maxMessage: 'Adres e-mail nie może być dłuższy niż {{ limit }} znaków.')]
    private string $email;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
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
