<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;
use InvalidArgumentException;
use PDOException;

final class UtilisateurFollowAction implements ActionInterface
{
    public function __construct(private readonly UtilisateurRepositoryInterface $repository) {}

    public function execute(array $parameters = []): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $followedId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $followerId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

        if ($followedId === false || $followedId < 1 ||
            $followerId === false || $followerId < 1) {
            http_response_code(400);
            echo json_encode(['error' => 'Utilisateur ou session invalide.']);
            return;
        }

        if ($this->repository->find((int) $followedId) === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Utilisateur à suivre introuvable.']);
            return;
        }

        if ((int) $followerId === (int) $followedId) {
            http_response_code(422);
            echo json_encode(['error' => 'Un utilisateur ne peut pas se suivre lui-même.']);
            return;
        }

        if ($this->repository->isFollowing((int) $followerId, (int) $followedId)) {
            echo json_encode(['message' => 'Vous suivez déjà cet utilisateur.']);
            return;
        }

        try {
            $this->repository->follow((int) $followerId, (int) $followedId);
        } catch (PDOException $exception) {
            error_log((string) $exception);
            http_response_code(409);
            echo json_encode(['error' => 'Impossible d’enregistrer le suivi.']);
            return;
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);
            echo json_encode(['error' => $exception->getMessage()]);
            return;
        }

        echo json_encode([
            'message' => 'Utilisateur suivi.',
            'followed_id' => (int) $followedId,
        ]);
    }
}
