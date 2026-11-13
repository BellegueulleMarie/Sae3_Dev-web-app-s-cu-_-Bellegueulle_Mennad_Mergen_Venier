<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TagRepositoryInterface;
use App\Contracts\UtilisateurRepositoryInterface;

final class AdminTrendAction implements ActionInterface
{
    public function __construct(
        private readonly UtilisateurRepositoryInterface $userRepository,
        private readonly TagRepositoryInterface $tagRepository
    ) {
    }

    public function execute(array $parameters = []): void
    {
        if (!AdminAuthentication::authorize($this->userRepository)) {
            return;
        }

        $tri = strtoupper((string) ($_GET['tri'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $tendances = $this->tagRepository->trends($tri);

        require dirname(__DIR__, 2) . '/views/admin-tendances.php';
    }
}
