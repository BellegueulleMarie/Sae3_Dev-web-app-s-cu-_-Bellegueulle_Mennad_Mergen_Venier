<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\TagRepositoryInterface;
use App\Contracts\TouiteRepositoryInterface;

/**
 * Classe TagPageAction
 *
 * Gère deux cas :
 * 1. Affichage de la liste de tous les tags (sans paramètre de tag)
 * 2. Affichage des touites d'un tag sélectionné (via id ou nom dans l'URL)
 *
 * Pour chaque tag, indique si l'utilisateur connecté y est abonné.
 */
final class TagPageAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param TagRepositoryInterface         $repository      Accès aux tags
     * @param TouiteRepositoryInterface|null $touiteRepository Accès aux touites (null si BDD non configurée)
     */
    public function __construct(
        private readonly TagRepositoryInterface $repository,
        private readonly ?TouiteRepositoryInterface $touiteRepository = null
    ) {
    }

    /**
     * Charge les données du tag (ou la liste) et affiche la vue.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (peut contenir 'id')
     */
    public function execute(array $parameters = []): void
    {
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

        // Récupère le tag depuis l'URL : par id (route) ou par nom (GET ?nom=php)
        $tagId   = filter_var($parameters['id'] ?? ($_GET['id'] ?? null), FILTER_VALIDATE_INT);
        $tagName = ltrim(trim((string) ($_GET['nom'] ?? '')), '#');

        $tagSelectionne = null;
        $touites        = [];
        $tags           = [];

        if ($tagId !== false && $tagId > 0) {
            $tagSelectionne = $this->repository->find((int) $tagId);
        } elseif ($tagName !== '') {
            $tagSelectionne = $this->repository->findByName($tagName);
        }

        if ($tagSelectionne !== null) {
            // Vue détail d'un tag : charge ses touites et le statut d'abonnement
            $tagSelectionne['is_subscribed'] = ($userId !== false && $userId > 0)
                ? $this->repository->isSubscribed((int) $userId, (int) $tagSelectionne['id'])
                : false;

            if ($this->touiteRepository !== null) {
                $touites = $this->touiteRepository->byTag((int) $tagSelectionne['id']);
            }
        } else {
            // Vue liste de tous les tags avec statut d'abonnement pour chacun
            $tags = $this->repository->all();
            if ($userId !== false && $userId > 0) {
                foreach ($tags as &$t) {
                    $t['is_subscribed'] = $this->repository->isSubscribed((int) $userId, (int) $t['id']);
                }
                unset($t); // Libère la référence
            }
        }

        require dirname(__DIR__, 2) . '/views/tags.php';
    }
}
