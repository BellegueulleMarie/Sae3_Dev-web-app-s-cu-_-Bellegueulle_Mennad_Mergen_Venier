<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;

final class UtilisateurLoginAction implements ActionInterface
{
    public function __construct(private readonly UtilisateurRepositoryInterface $repository) {}

    public function execute(array $parameters = []): void
    {
        $currentUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($currentUserId !== false && $currentUserId > 0) {
            header('Location: index.php?route=utilisateurs/' . (int) $currentUserId . '/profil');
            exit;
        }

        $error = '';
        $utilisateurs = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_var($_POST['utilisateur_id'] ?? null, FILTER_VALIDATE_INT);
            $utilisateur = $id !== false && $id > 0
                ? $this->repository->find((int) $id)
                : null;

            if ($utilisateur === null) {
                $error = 'Veuillez sélectionner un utilisateur valide.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $utilisateur['id_util'];
                $_SESSION['role'] = (string) $utilisateur['role'];
                header('Location: index.php?route=utilisateurs/' . (int) $utilisateur['id_util'] . '/profil');
                exit;
            }
        }

        $utilisateurs = $this->repository->all();
        require dirname(__DIR__, 2) . '/views/connexion.php';
    }
}
