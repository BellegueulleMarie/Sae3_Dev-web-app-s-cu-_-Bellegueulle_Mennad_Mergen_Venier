<?php

declare(strict_types=1);

namespace App\Http;

use App\Service\UtilisateurService;
use Throwable;

final class UtilisateurController
{
    public function __construct(private readonly UtilisateurService $service) {}

    public function index(): void { JsonResponse::send($this->service->list()); }

    public function store(): void
    {
        try {
            $this->service->create($this->input());
            JsonResponse::send(['message' => 'Utilisateur cree.'], 201);
        } catch (Throwable $exception) {
            JsonResponse::send(['error' => $exception->getMessage()], 400);
        }
    }

    /** @return array<string, mixed> */
    private function input(): array
    {
        $input = json_decode(file_get_contents('php://input') ?: '', true);
        return is_array($input) ? $input : $_POST;
    }
}
