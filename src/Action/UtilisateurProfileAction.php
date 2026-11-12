<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;
use App\Contracts\TouiteRepositoryInterface;

/**
 * Classe UtilisateurProfileAction
 *
 * Affiche le profil d'un utilisateur : ses informations, ses touites,
 * le nombre d'abonnés et son score moyen de likes par touite.
 *
 * Si aucun id n'est précisé dans l'URL, affiche le profil de l'utilisateur connecté.
 * Redirige vers la connexion si l'utilisateur n'est pas authentifié.
 */
final class UtilisateurProfileAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param UtilisateurRepositoryInterface $repository       Accès aux données utilisateurs
     * @param TouiteRepositoryInterface      $touiteRepository Accès aux touites
     */
    public function __construct(
        private readonly UtilisateurRepositoryInterface $repository,
        private readonly TouiteRepositoryInterface $touiteRepository
    ) {}

    /**
     * Charge les données du profil et affiche la vue.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (peut contenir 'id')
     */
    public function execute(array $parameters = []): void
    {
        // L'utilisateur doit être connecté pour consulter un profil
        $sessionId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($sessionId === false || $sessionId < 1) {
            header('Location: index.php?route=connexion');
            exit;
        }

        // Détermine l'id du profil à afficher (paramètre URL ou utilisateur connecté)
        $requestedId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $id = $requestedId !== false && $requestedId > 0
            ? (int) $requestedId
            : (int) $sessionId;

        $utilisateur = $this->repository->find($id);
        $touites     = $this->touiteRepository->byAuthor($id);

        // Vérifie si l'utilisateur connecté suit le profil consulté
        $isFollowing = $id !== (int) $sessionId &&
            $this->repository->isFollowing((int) $sessionId, $id);

        $followers  = [];
        $scoreMoyen = 0.0;

        if ($utilisateur !== null) {
            $followers  = $this->repository->followers((int) $id);
            $scoreMoyen = $this->touiteRepository->averageScore((int) $id);
            $utilisateur['followers_count'] = count($followers);
        } else {
            http_response_code(404);
        }

        require dirname(__DIR__, 2) . '/views/utilisateur-profile.php';
    }
}
