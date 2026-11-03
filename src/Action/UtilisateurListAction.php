<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;

final class UtilisateurListAction implements ActionInterface
{
    public function __construct(private readonly UtilisateurRepositoryInterface $repository) {}

    public function execute(array $parameters = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->repository->all());
    }
}
