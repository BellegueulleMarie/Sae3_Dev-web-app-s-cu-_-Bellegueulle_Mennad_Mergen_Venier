<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TouiteRepositoryInterface;

final class TouiteShowAction implements ActionInterface
{
    public function __construct(private readonly TouiteRepositoryInterface $repository) {}

    public function execute(array $parameters = []): void
    {
        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $touite = $id !== false && $id > 0
            ? $this->repository->find((int) $id)
            : null;

        if ($touite === null) {
            http_response_code(404);
        }

        require dirname(__DIR__, 2) . '/views/detailTouites.php';
    }
}
