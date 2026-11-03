<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;
use InvalidArgumentException;
use Throwable;

final class UtilisateurUpdateAction implements ActionInterface
{
    public function __construct(private readonly UtilisateurRepositoryInterface $repository) {}

    public function execute(array $parameters = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id = filter_var($parameters['id'] ?? null, FILTER_VALIDATE_INT);
            $data = $_POST;
            $nom = trim(filter_var((string) ($data['nom'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS));
            $prenom = trim(filter_var((string) ($data['prenom'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS));
            $email = trim(filter_var((string) ($data['email'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS));
            if ($id === false || $id < 1 || $nom === '' || $prenom === '' ||
                !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Les donnees utilisateur sont invalides.');
            }
            $this->repository->update($id, $nom, $prenom, $email);
            echo json_encode(['message' => 'Utilisateur modifie.']);
        } catch (InvalidArgumentException $exception) {
            http_response_code(400);
            echo json_encode(['error' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            error_log((string) $exception);
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur.']);
        }
    }
}
