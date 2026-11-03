<?php

declare(strict_types=1);

namespace App\Dao;

use App\Contracts\UtilisateurDaoInterface;
use PDO;

final class UtilisateurDao implements UtilisateurDaoInterface
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

    public function update(int $id, string $nom, string $prenom, string $email): void
    {
        $statement = $this->db->prepare(
            'UPDATE UTILISATEUR
             SET nom = :nom, prenom = :prenom, email = :email
             WHERE id_util = :id'
        );
        $statement->execute(compact('id', 'nom', 'prenom', 'email'));
    }
    public function delete(int $id): void {
        $statement = $this->db->prepare(
            'DELETE FROM UTILISATEUR WHERE id_util = :id'
        );
        $statement->execute(['id' => $id]);
    }
}
