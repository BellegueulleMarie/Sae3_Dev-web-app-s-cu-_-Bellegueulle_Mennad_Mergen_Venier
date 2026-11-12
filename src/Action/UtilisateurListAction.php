<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;

/**
 * Classe UtilisateurListAction
 *
 * Retourne la liste de tous les utilisateurs au format JSON.
 * Utilisée par les appels API (ex: sélecteur de connexion).
 */
final class UtilisateurListAction implements ActionInterface
{
    /**
     * Constructeur.
     *
     * @param UtilisateurRepositoryInterface $repository Accès aux données utilisateurs
     */
    public function __construct(private readonly UtilisateurRepositoryInterface $repository) {}

    /**
     * Retourne la liste JSON de tous les utilisateurs.
     *
     * @param array<string, string> $parameters Paramètres de l'URL (non utilisés ici)
     */
    public function execute(array $parameters = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->repository->all());
    }
}
