<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;

/**
 * Classe UtilisateurShowAction
 *
 * Retourne les informations d'un utilisateur au format JSON.
 * Inclut le nombre d'abonnés et le statut de suivi par l'utilisateur connecté.
 *
 * Route : GET /api/utilisateurs/{id}
 *
 * Codes HTTP :
 * - 200 : utilisateur trouvé
 * - 404 : utilisateur introuvable ou id invalide
 */
final class UtilisateurShowAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param UtilisateurRepositoryInterface $repository Accès aux données utilisateurs
     */
    public function __construct(private readonly UtilisateurRepositoryInterface $repository) {}

    /**
     * Retourne les données JSON de l'utilisateur demandé.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (['id' => '<user_id>'])
     */
    public function execute(array $parameters = []): void
    {
        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        header('Content-Type: application/json; charset=utf-8');

        if ($id === false || $id < 1) {
            http_response_code(404);
            echo json_encode(['error' => 'Utilisateur introuvable.']);
            return;
        }

        $user = $this->repository->find($id);
        if ($user === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Utilisateur introuvable.']);
            return;
        }

        // Enrichit la réponse avec les données de suivi
        $currentUserId  = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        $isAuthenticated = $currentUserId !== false && $currentUserId !== null && $currentUserId > 0;

        $user['followers_count'] = $this->repository->followerCount($id);
        $user['is_following']    = $isAuthenticated
            ? $this->repository->isFollowing((int) $currentUserId, $id)
            : false;
        $user['can_follow'] = $isAuthenticated && (int) $currentUserId !== $id;

        echo json_encode($user);
    }
}
