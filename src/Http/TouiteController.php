<?php

declare(strict_types=1);

namespace App\Http;

use App\Contracts\RequestDataReaderInterface;
use App\Contracts\ResponseInterface;
use App\Contracts\TouiteServiceInterface;
use Throwable;

final class TouiteController
{
    public function __construct(
        private readonly TouiteServiceInterface $service,
        private readonly RequestDataReaderInterface $request,
        private readonly ResponseInterface $response
    ) {}
    public function index(): void { $this->response->send($this->service->latest()); }
    public function store(): void
    {
        try {
            $this->service->create($this->request->read());
            $this->response->send(['message' => 'Touite cree.'], 201);
        } catch (Throwable $exception) {
            $this->response->send(['error' => $exception->getMessage()], 400);
        }
    }
}
