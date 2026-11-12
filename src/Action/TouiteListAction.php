<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TouiteRepositoryInterface;

/**
 * Classe TouiteListAction
 *
 * Affiche la liste paginée de tous les touites (ou ceux d'un auteur donné).
 * Supporte la pagination via le paramètre GET 'page' et le filtre auteur via 'author'.
 */
final class TouiteListAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param TouiteRepositoryInterface $repository Accès aux touites
     */
    public function __construct(private readonly TouiteRepositoryInterface $repository) {}

    /**
     * Calcule la pagination et charge les touites de la page courante.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (non utilisés ici)
     */
    public function execute(array $parameters = []): void
    {
        $perPage = 10;

        // Numéro de page depuis l'URL (?page=2), 1 par défaut
        $requestedPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        $page = $requestedPage !== false && $requestedPage > 0 ? $requestedPage : 1;

        // Filtre optionnel par auteur (?author=3)
        $requestedAuthorId = filter_var($_GET['author'] ?? null, FILTER_VALIDATE_INT);
        $authorId = $requestedAuthorId !== false && $requestedAuthorId > 0
            ? $requestedAuthorId
            : null;

        // Calcule le nombre total de pages
        $totalTouites = $this->repository->count($authorId);
        $totalPages   = (int) ceil($totalTouites / $perPage);

        // S'assure que la page demandée ne dépasse pas le total
        if ($totalPages > 0 && $page > $totalPages) {
            $page = $totalPages;
        }

        $touites = $this->repository->paginate($page, $perPage, $authorId);
        require dirname(__DIR__, 2) . '/views/pageTouites.php';
    }
}
