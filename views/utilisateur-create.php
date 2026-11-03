<?php

declare(strict_types=1);

function pageUtilisateur(string $error, string $success): void
{
    $error = htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
    $success = htmlspecialchars($success, ENT_QUOTES, 'UTF-8');

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
    afficherFormulaire();
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

function afficherFormulaire(): void
{
    echo <<<HTML
    <form method="post" action="/utilisateurs/creation">
        <p><label>Nom <input name="nom" required></label></p>
        <p><label>Prénom <input name="prenom" required></label></p>
        <p><label>Email <input name="email" type="email" required></label></p>
        <button type="submit">Créer</button>
    </form>
HTML;
}

pageUtilisateur($error ?? '', $success ?? '');
