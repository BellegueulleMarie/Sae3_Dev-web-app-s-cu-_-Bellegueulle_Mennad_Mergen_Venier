<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;
use InvalidArgumentException;
use PDOException;

/**
 * Classe UtilisateurFollowAction
 *
 * Gère le suivi et le désabonnement entre utilisateurs via AJAX.
 * Retourne une réponse JSON.
 *
 * Routes :
 * - POST /api/utilisateurs/{id}/suivre     → abonner
 * - POST /api/utilisateurs/{id}/desabonner → désabonner
 *
 * Codes HTTP :
 * - 200 : opération réussie
 * - 400 : paramètres invalides
 * - 404 : utilisateur cible introuvable
 * - 409 : erreur de contrainte BDD
 * - 422 : tentative de se suivre soi-même
 */
final class UtilisateurFollowAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param UtilisateurRepositoryInterface $repository Accès aux données utilisateurs
     */
    public function __construct(private readonly UtilisateurRepositoryInterface $repository) {}

    /**
     * Traite l'action suivre/désabonner et retourne une réponse JSON.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (['id' => '<user_id>'])
     */
    public function execute(array $parameters = []): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Détermine si c'est un désabonnement d'après l'URL
        $isUnfollow = str_contains((string) ($_SERVER['REQUEST_URI'] ?? ''), '/desabonner');

        $followedId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $followerId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

        // Vérifie que les identifiants sont valides
        if ($followedId === false || $followedId < 1 ||
            $followerId === false || $followerId < 1) {
            http_response_code(400);
            echo json_encode(['error' => 'Utilisateur ou session invalide.']);
            return;
        }

        // Vérifie que l'utilisateur cible existe
        if ($this->repository->find((int) $followedId) === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Utilisateur à suivre introuvable.']);
            return;
        }

        // On ne peut pas se suivre soi-même
        if ((int) $followerId === (int) $followedId) {
            http_response_code(422);
            echo json_encode(['error' => 'Un utilisateur ne peut pas se suivre lui-même.']);
            return;
        }

        // Si déjà suivi : gère la logique suivre/désabonner
        if ($this->repository->isFollowing((int) $followerId, (int) $followedId)) {
            if ($isUnfollow) {
                $this->repository->unfollow((int) $followerId, (int) $followedId);
                echo json_encode(['message' => 'Vous ne suivez plus cet utilisateur.']);
                return;
            }
            echo json_encode(['message' => 'Vous suivez déjà cet utilisateur.']);
            return;
        }

        if ($isUnfollow) {
            echo json_encode(['message' => 'Vous ne suivez pas cet utilisateur.']);
            return;
        }

        // Enregistre le suivi
        try {
            $this->repository->follow((int) $followerId, (int) $followedId);
        } catch (PDOException $exception) {
            error_log((string) $exception);
            http_response_code(409);
            echo json_encode(['error' => 'Impossible d\'enregistrer le suivi.']);
            return;
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);
            echo json_encode(['error' => $exception->getMessage()]);
            return;
        }

        echo json_encode([
            'message'     => 'Utilisateur suivi.',
            'followed_id' => (int) $followedId,
        ]);
    }
}
