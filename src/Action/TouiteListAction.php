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

        $perPage=10;
        $page = max(1, filter_var($parameters['page'] ?? 1, FILTER_VALIDATE_INT);

        $totalPages=(int) ceil($totalTouites / $perPage);
        $totalTouites= $this->repository->count();

        if( $page > $totalPages && $totalPages > 0) {
           $page= $totalPages;
        }
        $touites = $this->repository->paginate($page, $perPage);
        require dirname(__DIR__, 2) . '/views/pageTouites.php';
    }
}
