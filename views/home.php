<?php

declare(strict_types=1);

function pageAccueil(string $message, array $touites): void
{
    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    $isConnected = $userId !== false && $userId > 0;
    $navigation = $isConnected
        ? '<a class="profile-avatar" href="index.php?route=utilisateurs/' . (int) $userId .
            '/profil" aria-label="Voir mon profil">U</a>' .
          '<a class="button" href="index.php?route=utilisateurs/' . (int) $userId .
            '/profil">Mon profil</a>' .
          '<a class="button" href="index.php?route=utilisateurs">Utilisateurs</a>' .
          '<a class="button" href="index.php?route=tags">Gérer les tags</a>' .
          '<a class="button" href="index.php?route=statistiques">Statistiques</a>' .
          '<a class="button" href="index.php?route=deconnexion">Se déconnecter</a>'
        : '<a class="button" href="index.php?route=utilisateurs">Se connecter</a>' .
          '<a class="button" href="index.php?route=utilisateurs/creation">Créer un compte</a>';

    echo <<<HTML
        <!doctype html>
        <html lang="fr">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Touiteur.app</title>
            <link rel="stylesheet" href="CSS/utilisateur.css">
        </head>
        <body>
            <main>
                <h1>Touiteur.app</h1>
                <nav aria-label="Navigation principale">
                    $navigation
                    <a class="button" href="index.php?route=touites/nouveau">Écrire un touite</a>
                </nav>
                <h2>Mon mur</h2>
HTML;

    afficherContenuMur($message, $touites);

    echo <<<HTML
            </main>
            <script>
            document.querySelectorAll('.inline-tag-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const button = form.querySelector('button');
                    button.disabled = true;
                    fetch(form.action, {method: 'POST', body: new FormData(form)})
                        .then(function (response) {
                            return response.json().then(function (data) {
                                if (!response.ok) {
                                    throw new Error(data.error || 'Abonnement impossible.');
                                }
                                return data;
                            });
                        })
                        .then(function () {
                            button.textContent = 'Abonné';
                        })
                        .catch(function () {
                            button.disabled = false;
                        });
                });
            });
            </script>
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
    $auteur = echapper((string) ($touite['auteur'] ?? ''));
    $auteurId = (int) ($touite['id_auteur'] ?? 0);
    $auteurHtml = $auteurId > 0
        ? '<a href="index.php?route=utilisateurs/' . $auteurId . '/profil">' . $auteur . '</a>'
        : $auteur;
    $texte = afficherTags((string) ($touite['texte'] ?? ''));
    $date = echapper((string) ($touite['date_touite'] ?? ''));

    echo <<<HTML
        <article>
            <header>
                <strong aria-label="Auteur de la touite">$auteurHtml</strong>
            </header>
            <p>$texte</p>
            <small>$date</small>
        </article>
HTML;
}

function afficherTags(string $texte): string
{
    $texte = echapper($texte);
    $texte = preg_replace_callback(
        '/#([\p{L}\p{N}_-]+)/u',
        static function (array $match): string {
            $nom = $match[1];
            return '<form class="inline-tag-form" method="post" action="index.php?route=api/tags/abonner">' .
                '<input type="hidden" name="nom" value="' . echapper($nom) . '">' .
                '<button type="submit" class="hashtag">#' . echapper($nom) . '</button>' .
                '</form>';
        },
        $texte
    ) ?? $texte;

    return nl2br($texte);
}

function echapper(string $valeur): string
{
    return htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8');
}

pageAccueil($message ?? '', $touites ?? []);
