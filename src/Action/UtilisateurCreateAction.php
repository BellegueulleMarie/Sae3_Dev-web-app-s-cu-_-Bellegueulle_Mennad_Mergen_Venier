<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;

class UtilisateurCreateAction implements ActionInterface
{
    public function __construct(
        private readonly UtilisateurRepositoryInterface $repository
    )
    {
    }

    public function execute(array $parameters = []): void
    {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if ($nom === '' || $prenom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Données invalides.';
            } else {
                $this->repository->create($nom, $prenom, $email);
                $success = 'Utilisateur créé.';
            }
        }

        require dirname(__DIR__, 2) . '/views/utilisateur-create.php';
    }
}
