<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;

/**
 * Classe InfluencerAction
 *
 * Affiche la liste des influenceurs : les utilisateurs triés par nombre de suiveurs.
 * L'ordre par défaut est décroissant (du plus suivi au moins suivi).
 * L'utilisateur peut changer l'ordre via le paramètre GET 'tri' (ASC ou DESC).
 */
final class InfluencerAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param UtilisateurRepositoryInterface $repository Accès aux données utilisateurs
     */
    public function __construct(
        private readonly UtilisateurRepositoryInterface $repository
    ) {}

    /**
     * Charge les influenceurs et enrichit chaque entrée avec le statut de suivi
     * de l'utilisateur connecté, puis affiche la vue.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (non utilisés ici)
     */
    public function execute(array $parameters = []): void
    {
        $currentUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

        // Lecture du paramètre de tri depuis l'URL (?tri=ASC ou ?tri=DESC)
        $tri = strtoupper((string) ($_GET['tri'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $influenceurs = $this->repository->influencers($tri);

        // Pour chaque influenceur, vérifie si l'utilisateur connecté le suit déjà
        if ($currentUserId !== false && $currentUserId > 0) {
            foreach ($influenceurs as &$influenceur) {
                $influenceur['is_following'] = $this->repository->isFollowing(
                    (int) $currentUserId,
                    (int) $influenceur['id_util']
                );
            }
            unset($influenceur); // Libère la référence pour éviter les effets de bord
        }

        require dirname(__DIR__, 2) . '/views/influenceurs.php';
    }
}
