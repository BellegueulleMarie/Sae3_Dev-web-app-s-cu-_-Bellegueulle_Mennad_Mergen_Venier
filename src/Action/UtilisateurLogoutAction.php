<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;

/**
 * Classe UtilisateurLogoutAction
 *
 * Déconnecte l'utilisateur en vidant et détruisant sa session,
 * puis le redirige vers la page d'accueil.
 */
final class UtilisateurLogoutAction implements ActionInterface
{
    /**
     * Vide la session, la détruit et redirige vers l'accueil.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (non utilisés ici)
     */
    #[\NoReturn]
    public function execute(array $parameters = []): void
    {
        $_SESSION = [];
        session_destroy();
        header('Location: index.php');
        exit;
    }
}
