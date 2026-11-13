<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;
use PDOException;

/**
 * Classe UtilisateurCreateAction
 *
 * Gère la création d'un compte utilisateur.
 * - GET  /utilisateurs/creation : affiche le formulaire d'inscription
 * - POST /utilisateurs/creation : traite la soumission
 *
 * Si l'utilisateur est déjà connecté, il est redirigé vers son profil.
 */
class UtilisateurCreateAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param UtilisateurRepositoryInterface $repository Accès aux données utilisateurs
     */
    public function __construct(
        private readonly UtilisateurRepositoryInterface $repository
    )
    {
    }

    /**
     * Affiche le formulaire (GET) ou crée le compte (POST).
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

        $error   = '';
        $success = '';
        $values  = [
            'nom'    => '',
            'prenom' => '',
            'email'  => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $values['nom']    = trim((string) ($_POST['nom'] ?? ''));
            $values['prenom'] = trim((string) ($_POST['prenom'] ?? ''));
            $values['email']  = trim((string) ($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';
            $passwordConfirmation = $_POST['password_confirmation'] ?? '';
            $password = is_string($password) ? $password : '';
            $passwordConfirmation = is_string($passwordConfirmation) ? $passwordConfirmation : '';

            // Validation des champs
            if (
                $values['nom'] === '' ||
                $values['prenom'] === '' ||
                strlen($values['nom']) > 50 ||
                strlen($values['prenom']) > 50 ||
                strlen($values['email']) > 100 ||
                filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false
            ) {
                $error = 'Veuillez renseigner un nom, un prénom et une adresse e-mail valides.';
            } elseif (strlen($password) < 8 || strlen($password) > 72) {
                $error = 'Le mot de passe doit contenir entre 8 et 72 octets.';
            } elseif ($password !== $passwordConfirmation) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                try {
                    $this->repository->create(
                        $values['nom'],
                        $values['prenom'],
                        $values['email'],
                        $password
                    );
                    $success = 'Utilisateur créé.';
                    // Réinitialise le formulaire après succès
                    $values['nom']    = '';
                    $values['prenom'] = '';
                    $values['email']  = '';
                } catch (PDOException $exception) {
                    error_log((string) $exception);
                    $error = 'Impossible de créer cet utilisateur. Vérifiez que cette adresse e-mail n\'est pas déjà utilisée.';
                }
            }
        }

        require dirname(__DIR__, 2) . '/views/utilisateur-create.php';
    }
}
