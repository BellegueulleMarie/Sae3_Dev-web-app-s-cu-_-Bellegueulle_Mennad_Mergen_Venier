<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TagRepositoryInterface;

/**
 * Classe TrendAction
 *
 * Affiche les tendances : les tags triés par nombre d'utilisations dans les touites.
 * L'ordre par défaut est décroissant (tag le plus utilisé en premier).
 * L'utilisateur peut changer l'ordre via le paramètre GET 'tri' (ASC ou DESC).
 */
final class TrendAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param TagRepositoryInterface $tagRepository Accès aux données des tags
     */
    public function __construct(
        private readonly TagRepositoryInterface $tagRepository
    ) {}

    /**
     * Charge les tendances et affiche la vue.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (non utilisés ici)
     */
    public function execute(array $parameters = []): void
    {
        // Lecture du paramètre de tri depuis l'URL (?tri=ASC ou ?tri=DESC)
        $tri = strtoupper((string) ($_GET['tri'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
        $tendances = $this->tagRepository->trends($tri);

        require dirname(__DIR__, 2) . '/views/tendances.php';
    }
}
