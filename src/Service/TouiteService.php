<?php

declare(strict_types=1);

namespace App\Service;

use App\Contracts\TouiteDaoInterface;
use App\Contracts\TouiteServiceInterface;
use InvalidArgumentException;

final class TouiteService implements TouiteServiceInterface
{
    public function __construct(private readonly TouiteDaoInterface $dao) {}

    /** @return list<array<string, mixed>> */
    public function latest(): array { return $this->dao->latest(); }

    public function create(array $data): void
    {
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        $author = filter_var($data['id_auteur'] ?? null, FILTER_VALIDATE_INT);
        $text = trim((string) ($data['texte'] ?? ''));
        $image = isset($data['id_img']) && $data['id_img'] !== ''
            ? filter_var($data['id_img'], FILTER_VALIDATE_INT) : null;
        if ($id === false || $id < 1 || $author === false || $author < 1 ||
            $text === '' || mb_strlen($text) > 235 || ($image !== null && $image === false)) {
            throw new InvalidArgumentException('Les donnees de la touite sont invalides.');
        }
        $this->dao->create($id, $text, $author, $image);
    }
}
