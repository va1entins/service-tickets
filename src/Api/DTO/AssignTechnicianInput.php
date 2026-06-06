<?php

declare(strict_types=1);

namespace App\Api\DTO;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO dla operacji przypisania technika do zgłoszenia
 */
final class AssignTechnicianInput
{
    #[Assert\NotNull(message: 'Identyfikator technika jest wymagany.')]
    #[Assert\Positive(message: 'Identyfikator technika musi być liczbą dodatnią.')]
    #[Groups(['ticket:write'])]
    public int $technicianId;
}
