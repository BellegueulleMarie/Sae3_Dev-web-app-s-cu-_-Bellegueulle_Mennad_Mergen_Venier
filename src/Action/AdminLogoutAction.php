<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;

final class AdminLogoutAction implements ActionInterface
{
    public function execute(array $parameters = []): void
    {
        $_SESSION = [];
        session_destroy();

        $cookie = session_get_cookie_params();
        if (ini_get('session.use_cookies')) {
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $cookie['path'],
                'domain' => $cookie['domain'],
                'secure' => $cookie['secure'],
                'httponly' => $cookie['httponly'],
                'samesite' => $cookie['samesite'] ?? 'Strict',
            ]);
        }

        header('Location: admin.php?route=connexion');
        exit;
    }
}
