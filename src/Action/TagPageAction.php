<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TagRepositoryInterface;

final class TagPageAction implements ActionInterface
{
    public function __construct(private readonly TagRepositoryInterface $repository)
    {
    }

    public function execute(array $parameters = []): void
    {
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($userId === false || $userId < 1) {
            header('Location: index.php?route=connexion');
            exit;
        }

        $tags = $this->repository->all();
        foreach ($tags as &$tag) {
            $tag['is_subscribed'] = $this->repository->isSubscribed(
                (int) $userId,
                (int) $tag['id']
            );
        }
        unset($tag);

        require dirname(__DIR__, 2) . '/views/tags.php';
    }
}
