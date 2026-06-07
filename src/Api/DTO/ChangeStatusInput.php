<?php

declare(strict_types=1);

namespace App\Api\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO dla zmiany statusu zgłoszenia
 */
class ChangeStatusInput
{
    #[Assert\NotBlank(message: 'Status jest wymagany.')]
    public string $status;
}
