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
        // Récupère l'utilisateur connecté depuis la session
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        $utilisateur = $userId !== false && $userId > 0
            ? $this->repository->find((int) $userId)
            : null;

        // Vérifie le rôle admin
        $estAdministrateur = $utilisateur !== null && $utilisateur['role'] === 'admin';

        // Charge la liste triée seulement si admin
        $utilisateurs = $estAdministrateur
            ? $this->repository->usersByFollowerCount()
            : [];

        if (!$estAdministrateur) {
            http_response_code(403);
        }

        require dirname(__DIR__, 2) . '/views/admin-utilisateurs-suivis.php';
    }
}
