<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TouiteRepositoryInterface;

final class TouiteListAction implements ActionInterface
{
    public function __construct(private readonly TouiteRepositoryInterface $repository) {}

    public function execute(array $parameters = []): void
    {
        $perPage = 10;
        $requestedPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        $page = $requestedPage !== false && $requestedPage > 0 ? $requestedPage : 1;
        $requestedAuthorId = filter_var($_GET['author'] ?? null, FILTER_VALIDATE_INT);
        $authorId = $requestedAuthorId !== false && $requestedAuthorId > 0
            ? $requestedAuthorId
            : null;

        $totalTouites = $this->repository->count($authorId);
        $totalPages = (int) ceil($totalTouites / $perPage);

        if ($totalPages > 0 && $page > $totalPages) {
            $page = $totalPages;
        }
        $touites = $this->repository->paginate($page, $perPage, $authorId);
        require dirname(__DIR__, 2) . '/views/pageTouites.php';
    }
}
