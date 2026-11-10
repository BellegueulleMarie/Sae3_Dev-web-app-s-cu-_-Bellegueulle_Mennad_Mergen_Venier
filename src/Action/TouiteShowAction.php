<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TouiteRepositoryInterface;
use App\Contracts\UtilisateurRepositoryInterface;

final class TouiteShowAction implements ActionInterface
{
    public function __construct(
        private readonly TouiteRepositoryInterface $repository,
        private readonly UtilisateurRepositoryInterface $userRepository
    ) {}

    public function execute(array $parameters = []): void
    {
        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $touite = $id !== false && $id > 0
            ? $this->repository->find((int) $id)
            : null;

        if ($touite === null) {
            http_response_code(404);
        }
        $currentUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        $isFollowing = $currentUserId !== false && $currentUserId > 0 && $touite !== null &&
            (int) $touite['id_auteur'] !== (int) $currentUserId &&
            $this->userRepository->isFollowing((int) $currentUserId, (int) $touite['id_auteur']);

        require dirname(__DIR__, 2) . '/views/detailTouites.php';
    }
}
