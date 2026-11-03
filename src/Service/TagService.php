<?php

declare(strict_types=1);

namespace App\Service;

use App\Dao\TagDao;
use InvalidArgumentException;

final class TagService
{
    public function __construct(private readonly TagDao $dao) {}

    /** @return list<array<string, mixed>> */
    public function list(): array { return $this->dao->all(); }

    public function create(array $data): void
    {
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        $label = trim((string) ($data['libelle'] ?? ''));
        $description = isset($data['description']) ? trim((string) $data['description']) : null;
        if ($id === false || $id < 1 || $label === '') {
            throw new InvalidArgumentException('Les donnees du tag sont invalides.');
        }
        $this->dao->create($id, $label, $description ?: null);
    }
}
