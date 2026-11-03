<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\RequestDataReaderInterface;
use App\Contracts\ResponseInterface;
use App\Contracts\UtilisateurRepositoryInterface;
use InvalidArgumentException;
use Throwable;

final class UtilisateurUpdateAction implements ActionInterface
{
    public function __construct(
        private readonly UtilisateurRepositoryInterface $repository,
        private readonly RequestDataReaderInterface $request,
        private readonly ResponseInterface $response
    ) {
    }

    public function execute(array $parameters = []): void
    {
        try {
            $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
            $data = $this->request->read();
            $nom = trim((string) ($data['nom'] ?? ''));
            $prenom = trim((string) ($data['prenom'] ?? ''));
            $email = trim((string) ($data['email'] ?? ''));
            if ($id === false || $id < 1 || $nom === '' || $prenom === '' ||
                !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Les donnees utilisateur sont invalides.');
            }
            $this->repository->update($id, $nom, $prenom, $email);
            $this->response->send(['message' => 'Utilisateur modifie.']);
        } catch (Throwable $exception) {
            $this->response->send(['error' => $exception->getMessage()], 400);
        }
    }
}
