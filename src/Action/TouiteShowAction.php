<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TouiteRepositoryInterface;
use App\Contracts\UtilisateurRepositoryInterface;

/**
 * Classe TouiteShowAction
 *
 * Affiche le détail d'un touite : son contenu, l'auteur, le score (like/dislike),
 * le statut de suivi de l'auteur et le vote éventuel de l'utilisateur connecté.
 * Retourne une 404 si le touite n'existe pas.
 */
final class TouiteShowAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param TouiteRepositoryInterface      $repository     Accès aux touites
     * @param UtilisateurRepositoryInterface $userRepository Accès aux utilisateurs
     */
    public function __construct(
        private readonly TouiteRepositoryInterface $repository,
        private readonly UtilisateurRepositoryInterface $userRepository
    ) {}

    /**
     * Charge le touite et les données associées, puis affiche la vue détail.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (['id' => '<touite_id>'])
     */
    public function execute(array $parameters = []): void
    {
        // Récupère le touite demandé
        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $touite = $id !== false && $id > 0
            ? $this->repository->find((int) $id)
            : null;

        if ($touite === null) {
            http_response_code(404);
        }

        $currentUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

        // Vérifie si l'utilisateur connecté suit l'auteur du touite
        $isFollowing = $currentUserId !== false && $currentUserId > 0 && $touite !== null &&
            (int) $touite['id_auteur'] !== (int) $currentUserId &&
            $this->userRepository->isFollowing((int) $currentUserId, (int) $touite['id_auteur']);

        // Vérifie si l'utilisateur a déjà voté pour ce touite
        $hasEvaluated = $currentUserId !== false && $currentUserId > 0 && $touite !== null &&
            $this->repository->hasEvaluated((int) $currentUserId, (int) $touite['id_touite']);

        // Récupère la valeur du vote de l'utilisateur (+1, -1 ou null)
        $userEvaluation = $currentUserId !== false && $currentUserId > 0 && $touite !== null
            ? $this->repository->getUserEvaluation((int) $currentUserId, (int) $touite['id_touite'])
            : null;

        // Calcule le score total du touite
        $score = $touite !== null
            ? $this->repository->getScore((int) $touite['id_touite'])
            : 0;

        require dirname(__DIR__, 2) . '/views/detailTouites.php';
    }
}
