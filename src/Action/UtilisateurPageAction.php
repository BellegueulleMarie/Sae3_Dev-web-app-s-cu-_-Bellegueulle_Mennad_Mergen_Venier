<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;

final class UtilisateurPageAction implements ActionInterface
{
    public function __construct(private readonly UtilisateurRepositoryInterface $repository) {}

    public function execute(array $parameters = []): void
    {
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($userId === false || $userId < 1) {
            $utilisateurs = $this->repository->all();
            $error = '';
            require dirname(__DIR__, 2) . '/views/connexion.php';
            return;
        }

        $utilisateurs = $this->repository->all();
        foreach ($utilisateurs as &$utilisateur) {
            $utilisateur['is_following'] = $this->repository->isFollowing(
                (int) $userId,
                (int) $utilisateur['id_util']
            );
        }
        unset($utilisateur);
        $currentUserId = (int) $userId;
        require dirname(__DIR__, 2) . '/views/utilisateurs.php';
    }
}
