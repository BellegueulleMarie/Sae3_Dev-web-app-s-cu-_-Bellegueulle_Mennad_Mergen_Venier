<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;

final class AdminFollowersAction implements ActionInterface
{
    public function __construct(private readonly UtilisateurRepositoryInterface $repository) {}

    public function execute(array $parameters = []): void
    {
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        $utilisateur = $userId !== false && $userId > 0
            ? $this->repository->find((int) $userId)
            : null;
        $estAdministrateur = $utilisateur !== null && $utilisateur['role'] === 'admin';
        $utilisateurs = $estAdministrateur
            ? $this->repository->usersByFollowerCount()
            : [];

        if (!$estAdministrateur) {
            http_response_code(403);
        }

        require dirname(__DIR__, 2) . '/views/admin-utilisateurs-suivis.php';
    }
}
