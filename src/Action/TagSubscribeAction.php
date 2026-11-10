<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TagRepositoryInterface;

final class TagSubscribeAction implements ActionInterface
{
    public function __construct(private readonly TagRepositoryInterface $repository)
    {
    }

    public function execute(array $parameters = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        $tagId = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $tagName = mb_strtolower(trim((string) ($_POST['nom'] ?? '')));
        $isUnsubscribe = str_contains((string) ($_SERVER['REQUEST_URI'] ?? ''), '/desabonner');

        if ($userId === false || $userId < 1 ||
            (($tagId === false || $tagId < 1) && $tagName === '')) {
            http_response_code(400);
            echo json_encode(['error' => 'Utilisateur ou tag invalide.']);
            return;
        }
        $tag = $tagName !== ''
            ? $this->repository->findByName($tagName)
            : $this->repository->find((int) $tagId);
        if ($tag === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Tag introuvable.']);
            return;
        }

        $tagId = (int) $tag['id'];
        if ($isUnsubscribe) {
            $this->repository->unsubscribe((int) $userId, $tagId);
            echo json_encode(['message' => 'Vous êtes désabonné de ce tag.', 'tag_id' => $tagId]);
            return;
        }
        $this->repository->subscribe((int) $userId, $tagId);
        echo json_encode(['message' => 'Abonnement au tag enregistré.', 'tag_id' => $tagId]);
    }
}
