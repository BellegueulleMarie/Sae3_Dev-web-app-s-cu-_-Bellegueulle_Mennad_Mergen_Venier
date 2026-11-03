<?php

declare(strict_types=1);

namespace App\Http;

use App\Contracts\RequestDataReaderInterface;
use App\Contracts\ResponseInterface;
use App\Contracts\UtilisateurServiceInterface;
use Throwable;

final class UtilisateurController
{
    public function __construct(
        private readonly UtilisateurServiceInterface $service,
        private readonly RequestDataReaderInterface $request,
        private readonly ResponseInterface $response
    ) {}

    public function index(): void { $this->response->send($this->service->list()); }

    public function store(): void
    {
        try {
            $this->service->create($this->request->read());
            $this->response->send(['message' => 'Utilisateur cree.'], 201);
        } catch (Throwable $exception) {
            $this->response->send(['error' => $exception->getMessage()], 400);
        }
    }
    public function update(string $id): void
    {
        try {
            $this->service->update((int) $id, $this->request->read());
            $this->response->send(['message' => 'Utilisateur modifie.']);
        } catch (Throwable $exception) {
            $this->response->send(['error' => $exception->getMessage()], 400);
        }
    }
}
