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
        $touites = $this->repository->all();
        require dirname(__DIR__, 2) . '/views/pageTouites.php';
    }
}
