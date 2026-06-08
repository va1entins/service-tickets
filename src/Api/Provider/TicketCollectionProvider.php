<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Doctrine\Orm\Paginator;
use App\Entity\Ticket;
use App\Enum\TicketPriority;
use App\Enum\TicketStatus;
use App\Repository\TicketRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Dostawca kolekcji zgłoszeń serwisowych z filtrowaniem, sortowaniem i paginacją
 *
 * @implements ProviderInterface<Paginator<Ticket>>
 */
final class TicketCollectionProvider implements ProviderInterface
{
    private const int DEFAULT_PAGE  = 1;
    private const int DEFAULT_LIMIT = 10;
    private const int MAX_LIMIT     = 100;

    public function __construct(
        private readonly TicketRepository $ticketRepository,
        private readonly RequestStack $requestStack,
    ) {}

    /**
     * Zwraca przefiltrowaną i spaginowaną kolekcję zgłoszeń
     *
     * @return Paginator<Ticket>|array<Ticket>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator|array
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return [];
        }

        $filters = $this->resolveFilters($request->query->all());

        $qb = $this->ticketRepository->createFilteredQueryBuilder($filters);

        // Obliczenie offsetu dla paginacji
        $page  = max(self::DEFAULT_PAGE, (int) ($request->query->get('page', self::DEFAULT_PAGE)));
        $limit = min(self::MAX_LIMIT, max(1, (int) ($request->query->get('limit', self::DEFAULT_LIMIT))));

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $doctrinePaginator = new DoctrinePaginator($qb, fetchJoinCollection: true);

        return new Paginator($doctrinePaginator);
    }

    /**
     * Parsuje i waliduje parametry filtrowania z query stringa
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function resolveFilters(array $query): array
    {
        $filters = [];

        if (!empty($query['status'])) {
            $status = TicketStatus::tryFrom(strtoupper((string) $query['status']));
            if ($status !== null) {
                $filters['status'] = $status;
            }
        }

        if (!empty($query['priority'])) {
            $priority = TicketPriority::tryFrom(strtoupper((string) $query['priority']));
            if ($priority !== null) {
                $filters['priority'] = $priority;
            }
        }

        if (!empty($query['serialNumber'])) {
            $filters['serialNumber'] = (string) $query['serialNumber'];
        }

        $sortMap = [
            'createdAt' => 't.createdAt',
            'updatedAt' => 't.updatedAt',
            'priority'  => 't.priority',
            'status'    => 't.status',
        ];

        if (!empty($query['sort']) && isset($sortMap[$query['sort']])) {
            $filters['sortBy']  = $sortMap[$query['sort']];
            $filters['sortDir'] = strtoupper((string) ($query['order'] ?? 'DESC'));
        }

        return $filters;
    }
}
