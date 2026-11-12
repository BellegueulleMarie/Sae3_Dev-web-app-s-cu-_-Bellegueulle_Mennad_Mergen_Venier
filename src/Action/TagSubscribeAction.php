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
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        $tagId = filter_var($parameters['id'] ?? ($_GET['id'] ?? null), FILTER_VALIDATE_INT);
        $tagName = ltrim(mb_strtolower(trim((string) ($_POST['nom'] ?? ''))), '#');

        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $route = (string) ($_GET['route'] ?? '');
        $isUnsubscribe = str_contains($uri, 'desabonner') || str_contains($route, 'desabonner');

        // Détection si la requête provient d'un formulaire HTML pur (sans JavaScript)
        $accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
        $isPureHtmlForm = str_contains($accept, 'text/html') && !str_contains($accept, '*/*');

        if ($userId === false || $userId < 1) {
            if (!$isPureHtmlForm) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode(['error' => 'Vous devez être connecté.']);
                return;
            }
            header('Location: index.php?route=connexion');
            exit;
        }

        if (($tagId === false || $tagId < 1) && $tagName === '') {
            if (!$isPureHtmlForm) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['error' => 'Tag invalide.']);
                return;
            }
            header('Location: index.php?route=tags');
            exit;
        }

        $tag = $tagName !== ''
            ? $this->repository->findOrCreateByName($tagName)
            : $this->repository->find((int) $tagId);

        if ($tag === null) {
            if (!$isPureHtmlForm) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(404);
                echo json_encode(['error' => 'Tag introuvable.']);
                return;
            }
            header('Location: index.php?route=tags');
            exit;
        }

        $tagId = (int) $tag['id'];

        if ($isUnsubscribe) {
            $this->repository->unsubscribe((int) $userId, $tagId);
            if (!$isPureHtmlForm) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'message' => 'Vous êtes désabonné du tag #' . $tag['nom'] . '.',
                    'tag_id' => $tagId,
                    'is_subscribed' => false
                ]);
                return;
            }
            header('Location: index.php?route=tags/' . $tagId);
            exit;
        }

        $this->repository->subscribe((int) $userId, $tagId);
        if (!$isPureHtmlForm) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'message' => 'Abonnement au tag #' . $tag['nom'] . ' enregistré.',
                'tag_id' => $tagId,
                'nom' => $tag['nom'],
                'is_subscribed' => true
            ]);
            return;
        }

        header('Location: index.php?route=tags/' . $tagId);
        exit;
    }
}
