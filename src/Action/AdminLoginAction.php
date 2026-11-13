<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;

final class AdminLoginAction implements ActionInterface
{
    public function __construct(private readonly UtilisateurRepositoryInterface $repository)
    {
    }

    public function execute(array $parameters = []): void
    {
        $currentAdminId = filter_var($_SESSION['admin_user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($currentAdminId !== false && $currentAdminId > 0) {
            $currentAdmin = $this->repository->find((int) $currentAdminId);
            if ($currentAdmin !== null && ($currentAdmin['role'] ?? null) === 'admin') {
                header('Location: admin.php');
                exit;
            }
        }

        $error = '';
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $emailInput = $_POST['email'] ?? '';
            $email = is_string($emailInput) ? trim($emailInput) : '';
            $password = $_POST['password'] ?? '';
            if (!is_string($password)) {
                $password = '';
            }

            $adminId = filter_var($email, FILTER_VALIDATE_EMAIL) !== false
                ? $this->repository->authenticateAdmin($email, $password)
                : null;

            if ($adminId === null) {
                $error = 'Adresse e-mail ou mot de passe invalide.';
            } else {
                session_regenerate_id(true);
                $_SESSION['admin_user_id'] = $adminId;
                header('Location: admin.php');
                exit;
            }
        }

        require dirname(__DIR__, 2) . '/views/admin-connexion.php';
    }
}
