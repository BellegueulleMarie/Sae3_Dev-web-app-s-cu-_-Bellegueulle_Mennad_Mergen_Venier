<?php

declare(strict_types=1);

namespace App\Http;

use App\Service\TagService;
use Throwable;

final class TagController
{
    public function __construct(private readonly TagService $service) {}
    public function index(): void { JsonResponse::send($this->service->list()); }
    public function store(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input') ?: '', true);
            $this->service->create(is_array($input) ? $input : $_POST);
            JsonResponse::send(['message' => 'Tag cree.'], 201);
        } catch (Throwable $exception) {
            JsonResponse::send(['error' => $exception->getMessage()], 400);
        }
    }
}
