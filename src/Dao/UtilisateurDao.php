<?php

declare(strict_types=1);

namespace App\Dao;

use PDO;

final class UtilisateurDao
{
    public function __construct(private readonly PDO $db) {}

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return $this->db->query(
            'SELECT id_util, nom, prenom, email, nb_touites FROM UTILISATEUR ORDER BY id_util'
        )->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id_util, nom, prenom, email, nb_touites FROM UTILISATEUR WHERE id_util = :id'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();
        return $user === false ? null : $user;
    }

    public function create(int $id, string $nom, string $prenom, string $email): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO UTILISATEUR (id_util, nom, prenom, email) VALUES (:id, :nom, :prenom, :email)'
        );
        $statement->execute(compact('id', 'nom', 'prenom', 'email'));
    }
}
