<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;
use App\Contracts\TouiteRepositoryInterface;

final class UtilisateurProfileAction implements ActionInterface
{
    public function __construct(
        private readonly UtilisateurRepositoryInterface $repository,
        private readonly TouiteRepositoryInterface $touiteRepository
    ) {}

    public function execute(array $parameters = []): void
    {
        $sessionId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($sessionId === false || $sessionId < 1) {
            header('Location: index.php?route=connexion');
            exit;
        }

        $requestedId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $id = $requestedId !== false && $requestedId > 0
            ? (int) $requestedId
            : (int) $sessionId;
        $utilisateur = $this->repository->find($id);
        $touites = $this->touiteRepository->byAuthor($id);

        if ($utilisateur !== null) {
            $utilisateur['followers_count'] = $this->repository->followerCount((int) $id);
        } else {
            http_response_code(404);
        }

        require dirname(__DIR__, 2) . '/views/utilisateur-profile.php';
    }
}
