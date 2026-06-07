<?php

declare(strict_types=1);

namespace App\Api\DTO;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO dla zmiany statusu zgłoszenia
 */
final class ChangeStatusInput
{
    #[Assert\NotBlank(message: 'Status jest wymagany.')]
    #[Groups(['ticket:write'])]
    public string $status;
}
