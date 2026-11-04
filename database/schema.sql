CREATE DATABASE IF NOT EXISTS touiteur
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE touiteur;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS evaluations;
DROP TABLE IF EXISTS likes;
DROP TABLE IF EXISTS touite_tags;
DROP TABLE IF EXISTS abonnements_tags;
DROP TABLE IF EXISTS suivis;
DROP TABLE IF EXISTS touites;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS utilisateurs;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE utilisateurs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom_utilisateur VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('utilisateur', 'admin') NOT NULL DEFAULT 'utilisateur',
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE touites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auteur_id INT UNSIGNED NOT NULL,
    texte VARCHAR(235) NOT NULL,
    image VARCHAR(255) NULL,
    date_publication DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_touite_texte CHECK (CHAR_LENGTH(texte) BETWEEN 1 AND 235),
    CONSTRAINT fk_touite_auteur FOREIGN KEY (auteur_id)
        REFERENCES utilisateurs(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_touites_date (date_publication),
    INDEX idx_touites_auteur (auteur_id)
) ENGINE=InnoDB;

CREATE TABLE tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE touite_tags (
    touite_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (touite_id, tag_id),
    FOREIGN KEY (touite_id) REFERENCES touites(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE suivis (
    suiveur_id INT UNSIGNED NOT NULL,
    suivi_id INT UNSIGNED NOT NULL,
    date_suivi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (suiveur_id, suivi_id),
    CONSTRAINT chk_suivi_different CHECK (suiveur_id <> suivi_id),
    FOREIGN KEY (suiveur_id) REFERENCES utilisateurs(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (suivi_id) REFERENCES utilisateurs(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_suivis_suivi (suivi_id)
) ENGINE=InnoDB;

CREATE TABLE abonnements_tags (
    utilisateur_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    date_abonnement DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (utilisateur_id, tag_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE likes (
    utilisateur_id INT UNSIGNED NOT NULL,
    touite_id INT UNSIGNED NOT NULL,
    date_like DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (utilisateur_id, touite_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (touite_id) REFERENCES touites(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_likes_touite (touite_id)
) ENGINE=InnoDB;

CREATE TABLE evaluations (
    utilisateur_id INT UNSIGNED NOT NULL,
    touite_id INT UNSIGNED NOT NULL,
    valeur TINYINT NOT NULL,
    date_evaluation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (utilisateur_id, touite_id),
    CONSTRAINT chk_evaluation_valeur CHECK (valeur IN (-1, 1)),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (touite_id) REFERENCES touites(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
