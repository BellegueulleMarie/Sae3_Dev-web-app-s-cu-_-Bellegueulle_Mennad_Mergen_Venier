<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;

/**
 * Classe AdminFollowersAction
 *
 * Affiche la liste des utilisateurs triés par nombre de suiveurs,
 * réservée aux administrateurs.
 * Si l'utilisateur connecté n'est pas admin, une erreur 403 est retournée.
 */
final class AdminFollowersAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param UtilisateurRepositoryInterface $repository Accès aux données utilisateurs
     */
    public function __construct(private readonly UtilisateurRepositoryInterface $repository) {}

    /**
     * Vérifie que l'utilisateur est admin, puis charge la liste des utilisateurs.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (non utilisés ici)
     */
    public function execute(array $parameters = []): void
    {
        $estAdministrateur = AdminAuthentication::authorize($this->repository);
        if (!$estAdministrateur) {
            return;
        }

        $utilisateurs = $this->repository->usersByFollowerCount();

        require dirname(__DIR__, 2) . '/views/admin-utilisateurs-suivis.php';
    }
}
