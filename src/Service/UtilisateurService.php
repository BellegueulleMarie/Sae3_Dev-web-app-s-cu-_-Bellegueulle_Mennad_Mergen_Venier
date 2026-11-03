<?php

declare(strict_types=1);

namespace App\Service;

use App\Dao\UtilisateurDao;
use InvalidArgumentException;

final class UtilisateurService
{
    public function __construct(private readonly UtilisateurDao $dao) {}

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return $this->dao->all();
    }

    public function create(array $data): void
    {
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        $nom = trim((string) ($data['nom'] ?? ''));
        $prenom = trim((string) ($data['prenom'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        if ($id === false || $id < 1 || $nom === '' || $prenom === '' ||
            !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Les donnees utilisateur sont invalides.');
        }
        $this->dao->create($id, $nom, $prenom, $email);
    }
}
