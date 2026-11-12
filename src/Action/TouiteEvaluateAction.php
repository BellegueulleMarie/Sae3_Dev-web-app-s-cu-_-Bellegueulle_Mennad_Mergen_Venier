<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TouiteRepositoryInterface;

/**
 * Classe TouiteEvaluateAction
 *
 * Gère le vote (like/dislike) d'un touite via une requête POST AJAX.
 * Retourne une réponse JSON avec le message et le nouveau score.
 *
 * Codes HTTP retournés :
 * - 200 : vote enregistré avec succès
 * - 400 : identifiant du touite invalide
 * - 401 : utilisateur non connecté
 * - 404 : touite introuvable
 * - 409 : l'utilisateur a déjà voté
 */
final class TouiteEvaluateAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param TouiteRepositoryInterface $repository Accès aux touites
     */
    public function __construct(
        private readonly TouiteRepositoryInterface $repository
    ) {}

    /**
     * Traite le vote et retourne une réponse JSON.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (['id' => '<touite_id>'])
     */
    public function execute(array $parameters = []): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Vérifier que l'utilisateur est connecté
        $userId = filter_var(
            $_SESSION['user_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($userId === false || $userId < 1) {
            http_response_code(401);
            echo json_encode(['error' => 'Vous devez être connecté pour voter.']);
            return;
        }

        // Récupérer l'identifiant du touite depuis les paramètres de route
        $touiteId = filter_var(
            $parameters['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($touiteId === false || $touiteId < 1) {
            http_response_code(400);
            echo json_encode(['error' => 'Identifiant du touite invalide.']);
            return;
        }

        // Vérifier que le touite existe bien en base
        if ($this->repository->find((int) $touiteId) === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Touite introuvable.']);
            return;
        }

        // Empêcher un double vote
        if ($this->repository->hasEvaluated((int) $userId, (int) $touiteId)) {
            http_response_code(409);
            echo json_encode(['error' => 'Vous avez déjà voté pour ce touite.']);
            return;
        }

        // Lire la valeur du vote : +1 (like) ou -1 (dislike)
        $valeur = filter_var($_POST['valeur'] ?? 1, FILTER_VALIDATE_INT);
        $valeur = ($valeur !== false && $valeur < 0) ? -1 : 1;

        // Enregistrer le vote en base de données
        $this->repository->evaluate((int) $userId, (int) $touiteId, $valeur);

        // Retourner le nouveau score total du touite
        $score = $this->repository->getScore((int) $touiteId);

        echo json_encode([
            'message' => $valeur > 0 ? 'Votre vote positif a été enregistré.' : 'Votre vote négatif a été enregistré.',
            'score'   => $score,
            'valeur'  => $valeur,
        ]);
    }
}
