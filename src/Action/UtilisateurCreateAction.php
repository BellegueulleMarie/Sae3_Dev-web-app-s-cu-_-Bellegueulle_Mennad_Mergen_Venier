<?php

declare(strict_types=1);

namespace App\Action;

use App\Contracts\ActionInterface;
use App\Contracts\UtilisateurRepositoryInterface;
use InvalidArgumentException;
use Throwable;

final class UtilisateurCreateAction implements ActionInterface
{
    public function __construct(private readonly UtilisateurRepositoryInterface $repository) {}

    public function execute(array $parameters = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
            $nom = trim(filter_var($_POST['nom'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));
            $prenom = trim(filter_var($_POST['prenom'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));
            $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_SPECIAL_CHARS));
            if ($id === false || $id < 1 || $nom === '' || $prenom === '' ||
                !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('Donnees invalides.');
            }
            $this->repository->create($id, $nom, $prenom, $email);
            http_response_code(201);
            echo json_encode(['message' => 'Utilisateur cree.']);
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
