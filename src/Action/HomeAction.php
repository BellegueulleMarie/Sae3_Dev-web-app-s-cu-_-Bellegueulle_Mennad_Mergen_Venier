<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TouiteRepositoryInterface;

final class HomeAction implements ActionInterface
{
    public function __construct(
        private readonly ?TouiteRepositoryInterface $touiteRepository = null
    ) {
    }

    public function execute(array $parameters = []): void
    {
        $touites = [];
        $message = '';
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

        if ($this->touiteRepository === null) {
            $message = 'La base de données n’est pas configurée.';
        } elseif ($userId === false || $userId < 1) {
            $message = 'Connectez-vous pour consulter votre mur.';
        } else {
            $touites = $this->touiteRepository->wall($userId);
            if ($touites === []) {
                $message = 'Votre mur est vide : aucun utilisateur suivi ou tag suivi ne contient de touite à afficher.';
            }
        }

        require dirname(__DIR__, 2) . '/views/home.php';
    }
}
