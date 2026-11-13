<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;

final class AdminDashboardAction implements ActionInterface
{
    public function __construct(private readonly UtilisateurRepositoryInterface $repository)
    {
    }

    public function execute(array $parameters = []): void
    {
        if (!AdminAuthentication::authorize($this->repository)) {
            return;
        }

        require dirname(__DIR__, 2) . '/views/admin-accueil.php';
    }
}
