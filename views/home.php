<?php

declare(strict_types=1);

function pageAccueil(string $message, array $touites): void
{
    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    $isConnected = $userId !== false && $userId > 0;

    echo <<<HTML
        <!doctype html>
        <html lang="fr">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Touiteur.app</title>
            <link rel="stylesheet" href="/CSS/utilisateur.css">
        </head>
        <body>
            <main>
                <h1>Touiteur.app</h1>
                <nav aria-label="Navigation principale">
                    <?php if ($isConnected): ?>
                        <a class="profile-avatar" href="index.php?route=utilisateurs/<?= (int) $userId ?>/profil"
                           aria-label="Voir mon profil">U</a>
                        <a class="button" href="index.php?route=utilisateurs/<?= (int) $userId ?>/profil">Mon profil</a>
                        <a class="button" href="index.php?route=deconnexion">Se déconnecter</a>
                    <?php else: ?>
                        <a class="button" href="index.php?route=utilisateurs">Se connecter</a>
                        <a class="button" href="index.php?route=utilisateurs/creation">Créer un compte</a>
                    <?php endif; ?>
                    <a class="button" href="index.php?route=touites">Voir les touites</a>
                </nav>
                <h2>Mon mur</h2>
HTML;

    afficherContenuMur($message, $touites);

    echo <<<HTML
            </main>
        </body>
        </html>
HTML;
}

function afficherContenuMur(string $message, array $touites): void
{
    if ($message !== '') {
        afficherMessageMur($message);
        return;
    }

    if ($touites === []) {
        afficherMessageMur('Aucune touite à afficher.');
        return;
    }

    echo '<section aria-label="Touites de mon mur">';
    foreach ($touites as $touite) {
        afficherTouite($touite);
    }
    echo '</section>';
}

function afficherMessageMur(string $message): void
{
    echo '<p>' . echapper($message) . '</p>';
}

/** @param array<string, mixed> $touite */
function afficherTouite(array $touite): void
{
    $texte = nl2br(echapper((string) ($touite['texte'] ?? '')));
    $date = echapper((string) ($touite['date_touite'] ?? ''));

    echo <<<HTML
        <article>
            <p>$texte</p>
            <small>$date</small>
        </article>
HTML;
}

function echapper(string $valeur): string
{
    return htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8');
}

pageAccueil($message ?? '', $touites ?? []);
