<?php

declare(strict_types=1);

namespace App\Http;

use App\Contracts\RequestDataReaderInterface;
use App\Contracts\ResponseInterface;
use App\Contracts\TagServiceInterface;
use Throwable;

final class TagController
{
    public function __construct(
        private readonly TagServiceInterface $service,
        private readonly RequestDataReaderInterface $request,
        private readonly ResponseInterface $response
    ) {}
    public function index(): void { $this->response->send($this->service->list()); }
    public function store(): void
    {
        try {
            $this->service->create($this->request->read());
            $this->response->send(['message' => 'Tag cree.'], 201);
        } catch (Throwable $exception) {
            $this->response->send(['error' => $exception->getMessage()], 400);
        }
    }
}
