<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\ResponseInterface;
use App\Contracts\UtilisateurRepositoryInterface;

final class UtilisateurShowAction implements ActionInterface
{
    public function __construct(
        private readonly UtilisateurRepositoryInterface $repository,
        private readonly ResponseInterface $response
    ) {
    }

    public function execute(array $parameters = []): void
    {
        $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
        $user = $id !== false && $id > 0 ? $this->repository->find($id) : null;
        $this->response->send(
            $user ?? ['error' => 'Utilisateur introuvable.'],
            $user === null ? 404 : 200
        );
    }
}
