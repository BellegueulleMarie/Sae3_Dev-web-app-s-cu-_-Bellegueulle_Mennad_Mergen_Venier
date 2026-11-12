<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TagRepositoryInterface;
use App\Contracts\TouiteRepositoryInterface;
use App\Contracts\UtilisateurRepositoryInterface;

final class StatisticsAction implements ActionInterface
{
    public function __construct(
        private readonly TagRepositoryInterface $tagRepository,
        private readonly ?TouiteRepositoryInterface $touiteRepository = null,
        private readonly ?UtilisateurRepositoryInterface $userRepository = null
    ) {
    }

    public function execute(array $parameters = []): void
    {
        $statistics = $this->tagRepository->statistics();

        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        $utilisateur = null;
        $followers = [];
        $scoreMoyen = 0.0;

        // Si l'utilisateur est connecté, on récupère ses stats narcissiques
        if ($userId !== false && $userId > 0 && $this->userRepository !== null && $this->touiteRepository !== null) {
            $utilisateur = $this->userRepository->find((int) $userId);
            $followers = $this->userRepository->followers((int) $userId);
            $scoreMoyen = $this->touiteRepository->averageScore((int) $userId);
        }

        require dirname(__DIR__, 2) . '/views/statistiques.php';
    }
}
