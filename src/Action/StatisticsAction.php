<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TagRepositoryInterface;
use App\Contracts\TouiteRepositoryInterface;
use App\Contracts\UtilisateurRepositoryInterface;

/**
 * Classe StatisticsAction
 *
 * Affiche la page de statistiques globales et personnelles.
 * - Statistiques globales : nombre de touites, likes et moyenne par tag
 * - Statistiques personnelles (si connecté) : abonnés, score moyen de l'utilisateur
 */
final class StatisticsAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param TagRepositoryInterface              $tagRepository    Accès aux statistiques des tags
     * @param TouiteRepositoryInterface|null      $touiteRepository Accès aux scores de touites (null si BDD non configurée)
     * @param UtilisateurRepositoryInterface|null $userRepository   Accès aux données utilisateurs (null si BDD non configurée)
     */
    public function __construct(
        private readonly TagRepositoryInterface $tagRepository,
        private readonly ?TouiteRepositoryInterface $touiteRepository = null,
        private readonly ?UtilisateurRepositoryInterface $userRepository = null
    ) {
    }

    /**
     * Charge les statistiques et affiche la vue.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (non utilisés ici)
     */
    public function execute(array $parameters = []): void
    {
        // Statistiques globales par tag
        $statistics = $this->tagRepository->statistics();

        $userId      = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        $utilisateur = null;
        $followers   = [];
        $scoreMoyen  = 0.0;

        // Statistiques personnelles si l'utilisateur est connecté
        if ($userId !== false && $userId > 0 && $this->userRepository !== null && $this->touiteRepository !== null) {
            $utilisateur = $this->userRepository->find((int) $userId);
            $followers   = $this->userRepository->followers((int) $userId);
            $scoreMoyen  = $this->touiteRepository->averageScore((int) $userId);
        }

        require dirname(__DIR__, 2) . '/views/statistiques.php';
    }
}
