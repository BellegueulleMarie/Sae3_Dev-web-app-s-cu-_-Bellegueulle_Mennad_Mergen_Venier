<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;
use PDOException;

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
        $values = [
            'nom' => '',
            'prenom' => '',
            'email' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $values['nom'] = trim((string) ($_POST['nom'] ?? ''));
            $values['prenom'] = trim((string) ($_POST['prenom'] ?? ''));
            $values['email'] = trim((string) ($_POST['email'] ?? ''));

            if (
                $values['nom'] === '' ||
                $values['prenom'] === '' ||
                strlen($values['nom']) > 50 ||
                strlen($values['prenom']) > 50 ||
                strlen($values['email']) > 100 ||
                filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false
            ) {
                $error = 'Veuillez renseigner un nom, un prénom et une adresse e-mail valides.';
            } else {
                try {
                    $this->repository->create(
                        $values['nom'],
                        $values['prenom'],
                        $values['email']
                    );
                    $success = 'Utilisateur créé.';
                    $values['nom'] = '';
                    $values['prenom'] = '';
                    $values['email'] = '';
                } catch (PDOException $exception) {
                    error_log((string) $exception);
                    $error = 'Impossible de créer cet utilisateur. Vérifiez que cette adresse e-mail n’est pas déjà utilisée.';
                }
            }
        }

        require dirname(__DIR__, 2) . '/views/utilisateur-create.php';
    }
}
