<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Ticket;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Voter kontrolujący dostęp do zgłoszeń serwisowych.
 *
 * @extends Voter<string, Ticket>
 *
 * Obsługiwane atrybuty:
 *   - VIEW: każdy zalogowany użytkownik
 *   - EDIT: admin lub technik przypisany do zgłoszenia
 */
class TicketVoter extends Voter
{
    public const string VIEW = 'TICKET_VIEW';
    public const string EDIT = 'TICKET_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT], true)
            && $subject instanceof Ticket;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Ticket $ticket */
        $ticket = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($user),
            self::EDIT => $this->canEdit($ticket, $user),
            default    => false,
        };
    }

    /**
     * Każdy zalogowany użytkownik może przeglądać zgłoszenia.
     */
    private function canView(UserInterface $user): bool
    {
        return true;
    }

    /**
     * Admin może edytować wszystko.
     * Technik może edytować tylko zgłoszenia przypisane do siebie.
     */
    private function canEdit(Ticket $ticket, UserInterface $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        if (!in_array('ROLE_TECHNICIAN', $user->getRoles(), true)) {
            return false;
        }

        $assignedTechnician = $ticket->getAssignedTechnician();

        if ($assignedTechnician === null) {
            return false;
        }

        return $assignedTechnician->getEmail() === $user->getUserIdentifier();
    }
}
