<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\UtilisateurRepositoryInterface;

final class AdminAuthentication
{
    public static function authorize(UtilisateurRepositoryInterface $repository): bool
    {
        $adminId = filter_var($_SESSION['admin_user_id'] ?? null, FILTER_VALIDATE_INT);
        if ($adminId === false || $adminId < 1) {
            header('Location: admin.php?route=connexion');
            return false;
        }

        $admin = $repository->find((int) $adminId);
        if ($admin === null || ($admin['role'] ?? null) !== 'admin') {
            unset($_SESSION['admin_user_id']);
            http_response_code(403);
            echo '<h1>Accès réservé aux administrateurs.</h1>';
            return false;
        }

        return true;
    }
}
