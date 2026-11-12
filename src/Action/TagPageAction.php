<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TagRepositoryInterface;
use App\Contracts\TouiteRepositoryInterface;

final class TagPageAction implements ActionInterface
{
    public function __construct(
        private readonly TagRepositoryInterface $repository,
        private readonly ?TouiteRepositoryInterface $touiteRepository = null
    ) {
    }

    public function execute(array $parameters = []): void
    {
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

        $tagId = filter_var($parameters['id'] ?? ($_GET['id'] ?? null), FILTER_VALIDATE_INT);
        $tagName = ltrim(trim((string) ($_GET['nom'] ?? '')), '#');

        $tagSelectionne = null;
        $touites = [];
        $tags = [];

        if ($tagId !== false && $tagId > 0) {
            $tagSelectionne = $this->repository->find((int) $tagId);
        } elseif ($tagName !== '') {
            $tagSelectionne = $this->repository->findByName($tagName);
        }

        if ($tagSelectionne !== null) {
            // Détail d'un tag : on récupère ses touites et le statut d'abonnement
            $tagSelectionne['is_subscribed'] = ($userId !== false && $userId > 0)
                ? $this->repository->isSubscribed((int) $userId, (int) $tagSelectionne['id'])
                : false;

            if ($this->touiteRepository !== null) {
                $touites = $this->touiteRepository->byTag((int) $tagSelectionne['id']);
            }
        } else {
            // Liste de tous les tags
            $tags = $this->repository->all();
            if ($userId !== false && $userId > 0) {
                foreach ($tags as &$t) {
                    $t['is_subscribed'] = $this->repository->isSubscribed((int) $userId, (int) $t['id']);
                }
                unset($t);
            }
        }

        require dirname(__DIR__, 2) . '/views/tags.php';
    }
}
