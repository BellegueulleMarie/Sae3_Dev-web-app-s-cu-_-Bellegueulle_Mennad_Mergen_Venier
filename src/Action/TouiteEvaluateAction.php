<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TouiteRepositoryInterface;

final class TouiteEvaluateAction implements ActionInterface
{
    public function __construct(
        private readonly TouiteRepositoryInterface $repository
    ) {}

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

        // Récupérer l'identifiant du touite
        $touiteId = filter_var(
            $parameters['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($touiteId === false || $touiteId < 1) {
            http_response_code(400);
            echo json_encode(['error' => 'Identifiant du touite invalide.']);
            return;
        }

        // Vérifier que le touite existe
        if ($this->repository->find((int) $touiteId) === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Touite introuvable.']);
            return;
        }

        // Vérifier si l'utilisateur a déjà voté
        if ($this->repository->hasEvaluated((int) $userId, (int) $touiteId)) {
            http_response_code(409);
            echo json_encode(['error' => 'Vous avez déjà voté pour ce touite.']);
            return;
        }

        // Enregistrer le vote positif
        $this->repository->evaluatePositive((int) $userId, (int) $touiteId);

        // Récupérer le nouveau score
        $score = $this->repository->getScore((int) $touiteId);

        echo json_encode([
            'message' => 'Votre vote a été enregistré.',
            'score' => $score,
        ]);
    }
}