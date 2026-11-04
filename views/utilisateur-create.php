<?php

declare(strict_types=1);

function pageUtilisateur(string $error, string $success, array $values): void
{
    $error = htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
    $success = htmlspecialchars($success, ENT_QUOTES, 'UTF-8');
    $nom = htmlspecialchars((string) ($values['nom'] ?? ''), ENT_QUOTES, 'UTF-8');
    $prenom = htmlspecialchars((string) ($values['prenom'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars((string) ($values['email'] ?? ''), ENT_QUOTES, 'UTF-8');

    echo <<<HTML
        <!doctype html>
        <html lang="fr">
        <head>
            <meta charset="utf-8">
            <title>Créer un utilisateur</title>
            <link rel="stylesheet" href="/CSS/utilisateur.css">
        </head>
        <body>
            <h1>Créer un utilisateur</h1>
HTML;
    afficherMessage($error, 'Erreur');
    afficherMessage($success, 'Succès');
    afficherFormulaire($nom, $prenom, $email);
    echo '<p><a class="button" href="index.php">Retour à l’accueil</a></p>';
    echo '</body></html>';
}

function afficherMessage(string $message, string $titre): void
{
    if ($message !== '') {
        $titre = htmlspecialchars($titre, ENT_QUOTES, 'UTF-8');
        echo <<<HTML
    <p><strong>$titre :</strong> $message</p>
HTML;
    }
}

function afficherFormulaire(string $nom, string $prenom, string $email): void
{
    echo <<<HTML
    <form method="post" action="index.php?route=utilisateurs/creation">
        <p><label for="nom">Nom</label>
            <input id="nom" name="nom" maxlength="50" value="$nom" required></p>
        <p><label for="prenom">Prénom</label>
            <input id="prenom" name="prenom" maxlength="50" value="$prenom" required></p>
        <p><label for="email">Email</label>
            <input id="email" name="email" type="email" maxlength="100" value="$email" required></p>
        <button type="submit">Créer</button>
    </form>
HTML;
}

pageUtilisateur($error ?? '', $success ?? '', $values ?? []);
