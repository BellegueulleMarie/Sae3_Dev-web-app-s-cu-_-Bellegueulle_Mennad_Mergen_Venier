<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;

/**
 * Classe UtilisateurPageAction
 *
 * Affiche la liste de tous les utilisateurs avec leur statut de suivi
 * par rapport à l'utilisateur connecté.
 * Si l'utilisateur n'est pas connecté, affiche la page de connexion.
 */
final class UtilisateurPageAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param UtilisateurRepositoryInterface $repository Accès aux données utilisateurs
     */
    public function __construct(private readonly UtilisateurRepositoryInterface $repository) {}

    /**
     * Charge la liste des utilisateurs et enrichit chacun avec is_following,
     * puis affiche la vue.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (non utilisés ici)
     */
    public function execute(array $parameters = []): void
    {
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

        // Si non connecté, affiche la page de connexion à la place
        if ($userId === false || $userId < 1) {
            $utilisateurs = $this->repository->all();
            $error        = '';
            require dirname(__DIR__, 2) . '/views/connexion.php';
            return;
        }

        // Charge tous les utilisateurs et indique pour chacun si l'utilisateur connecté les suit
        $utilisateurs = $this->repository->all();
        foreach ($utilisateurs as &$utilisateur) {
            $utilisateur['is_following'] = $this->repository->isFollowing(
                (int) $userId,
                (int) $utilisateur['id_util']
            );
        }
        unset($utilisateur); // Libère la référence

        $currentUserId = (int) $userId;
        require dirname(__DIR__, 2) . '/views/utilisateurs.php';
    }
}
