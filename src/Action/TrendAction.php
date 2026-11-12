<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TagRepositoryInterface;

final class TrendAction implements ActionInterface
{
    public function __construct(
        private readonly TagRepositoryInterface $tagRepository
    ) {}

    public function execute(array $parameters = []): void
    {
        $tri = strtoupper((string) ($_GET['tri'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $tendances = $this->tagRepository->trends($tri);

        require dirname(__DIR__, 2) . '/views/tendances.php';
    }
}
