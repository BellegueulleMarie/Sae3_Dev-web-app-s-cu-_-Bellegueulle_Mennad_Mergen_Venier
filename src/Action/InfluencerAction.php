<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;

final class InfluencerAction implements ActionInterface
{
    public function __construct(
        private readonly UtilisateurRepositoryInterface $repository
    ) {}

    public function execute(array $parameters = []): void
    {
        $currentUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        $tri = strtoupper((string) ($_GET['tri'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $influenceurs = $this->repository->influencers($tri);

        if ($currentUserId !== false && $currentUserId > 0) {
            foreach ($influenceurs as &$influenceur) {
                $influenceur['is_following'] = $this->repository->isFollowing(
                    (int) $currentUserId,
                    (int) $influenceur['id_util']
                );
            }
            unset($influenceur);
        }

        require dirname(__DIR__, 2) . '/views/influenceurs.php';
    }
}
