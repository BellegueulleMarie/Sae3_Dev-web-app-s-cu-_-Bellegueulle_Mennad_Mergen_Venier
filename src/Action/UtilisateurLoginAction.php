<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;

/**
 * Classe UtilisateurLoginAction
 *
 * Gère la connexion des utilisateurs.
 * - GET  /connexion : affiche le formulaire de sélection d'utilisateur
 * - POST /connexion : crée la session et redirige vers le profil
 *
 * Note : il n'y a pas de mot de passe dans cette application (simulation SAE).
 * L'utilisateur se connecte simplement en choisissant son compte dans la liste.
 */
final class UtilisateurLoginAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param \App\Contracts\UtilisateurRepositoryInterface $repository Accès aux données utilisateurs
     */
    public function __construct(private readonly \App\Contracts\UtilisateurRepositoryInterface $repository) {}

    /**
     * Affiche le formulaire (GET) ou crée la session (POST).
     * Si l'utilisateur est déjà connecté, le redirige vers son profil.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (non utilisés ici)
     */
    public function execute(array $parameters = []): void
    {
        // Redirige si déjà connecté
        $currentUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($currentUserId !== false && $currentUserId > 0) {
            header('Location: index.php?route=utilisateurs/' . (int) $currentUserId . '/profil');
            exit;
        }

        $error        = '';
        $utilisateurs = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['utilisateur_id'] ?? null, FILTER_VALIDATE_INT);
            $utilisateur = $id !== false && $id > 0
                ? $this->repository->find((int) $id)
                : null;

            if ($utilisateur === null) {
                $error = 'Veuillez sélectionner un utilisateur valide.';
            } else {
                // Régénère l'ID de session pour éviter la fixation de session
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $utilisateur['id_util'];
                $role = (string) ($utilisateur['role'] ?? 'utilisateur');
                // Stocke dans les deux clés pour compatibilité entre branches
                $_SESSION['role']      = $role;
                $_SESSION['user_role'] = $role;
                header('Location: index.php?route=utilisateurs/' . (int) $utilisateur['id_util'] . '/profil');
                exit;
            }
        }

        $utilisateurs = $this->repository->all();
        require dirname(__DIR__, 2) . '/views/connexion.php';
    }
}
