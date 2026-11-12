<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TouiteRepositoryInterface;

/**
 * Classe HomeAction
 *
 * Affiche le mur de l'utilisateur connecté : les touites des personnes qu'il suit
 * et des tags auxquels il est abonné, triés du plus récent au plus ancien.
 * Si l'utilisateur n'est pas connecté, un message d'invitation s'affiche.
 */
final class HomeAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param TouiteRepositoryInterface|null $touiteRepository Accès aux touites (null si BDD non configurée)
     */
    public function __construct(
        private readonly ?TouiteRepositoryInterface $touiteRepository = null
    ) {
    }

    /**
     * Charge les touites du mur et affiche la page d'accueil.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (non utilisés ici)
     */
    public function execute(array $parameters = []): void
    {
        $touites = [];
        $message = '';
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

        if ($this->touiteRepository === null) {
            $message = 'La base de données n\'est pas configurée.';
        } elseif ($userId === false || $userId < 1) {
            $message = 'Connectez-vous pour consulter votre mur.';
        } else {
            $touites = $this->touiteRepository->wall($userId);
            if ($touites === []) {
                $message = 'Votre mur est vide : suivez un utilisateur ou abonnez-vous à un tag.';
            }
        }

        require dirname(__DIR__, 2) . '/views/home.php';
    }
}
