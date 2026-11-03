<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;
use App\Contracts\ResponseInterface;

final class UtilisateurListAction implements ActionInterface
{
    public function __construct(
        private readonly UtilisateurRepositoryInterface $repository,
        private readonly ResponseInterface $response
    ) {
    }

    public function execute(array $parameters = []): void
    {
        $this->response->send($this->repository->all());
    }
}
