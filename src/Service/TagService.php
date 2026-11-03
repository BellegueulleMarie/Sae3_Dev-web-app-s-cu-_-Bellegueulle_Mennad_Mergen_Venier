<?php

declare(strict_types=1);

namespace App\Service;

use App\Contracts\TagDaoInterface;
use App\Contracts\TagServiceInterface;
use InvalidArgumentException;

final class TagService implements TagServiceInterface
{
    public function __construct(private readonly TagDaoInterface $dao) {}

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
