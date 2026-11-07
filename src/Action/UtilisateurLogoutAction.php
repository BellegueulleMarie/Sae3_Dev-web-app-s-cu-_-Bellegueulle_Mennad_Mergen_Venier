<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;

final class UtilisateurLogoutAction implements ActionInterface
{
    #[\NoReturn]
    public function execute(array $parameters = []): void
    {
        $_SESSION = [];
        session_destroy();
        header('Location: index.php');
        exit;
    }
}
