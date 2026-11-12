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
          (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'
              ? '<a class="button" href="index.php?route=administration/utilisateurs-suivis">Suiveurs</a>'
              : '') .
          '<a class="button" href="index.php?route=deconnexion">Se déconnecter</a>'
        : '<a class="button" href="index.php?route=utilisateurs">Se connecter</a>' .
          '<a class="button" href="index.php?route=utilisateurs/creation">Créer un compte</a>';

    $tagFormHtml = $isConnected
        ? '<section class="stat-box">' .
          '<h3>S’abonner à un tag</h3>' .
          '<p>Entrez un tag pour afficher ses touites sur votre mur :</p>' .
          '<form class="wall-tag-form" method="post" action="index.php?route=api/tags/abonner">' .
          '<input type="text" name="nom" placeholder="ex: php, web..." required>' .
          '<button type="submit">S’abonner</button>' .
          '<span class="wall-tag-status" role="status"></span>' .
          '</form>' .
          '</section>'
        : '';

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
                    $navigation
                    <a class="button" href="index.php?route=touites/nouveau">Écrire un touite</a>
                </nav>
                $tagFormHtml
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

            const wallTagForm = document.querySelector('.wall-tag-form');
            if (wallTagForm) {
                wallTagForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const button = wallTagForm.querySelector('button');
                    const input = wallTagForm.querySelector('input[name="nom"]');
                    const status = wallTagForm.querySelector('.wall-tag-status');
                    button.disabled = true;
                    status.textContent = 'Abonnement...';

                    fetch(wallTagForm.action, {
                        method: 'POST',
                        headers: {'Accept': 'application/json'},
                        body: new FormData(wallTagForm)
                    })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            if (!response.ok) {
                                throw new Error(data.error || 'Erreur.');
                            }
                            return data;
                        });
                    })
                    .then(function (data) {
                        status.textContent = data.message;
                        input.value = '';
                        button.disabled = false;
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000);
                    })
                    .catch(function (error) {
                        status.textContent = error.message;
                        button.disabled = false;
                    });
                });
            }
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
            return '<a class="hashtag" href="index.php?route=tags&amp;nom=' . urlencode($nom) . '">#' . echapper($nom) . '</a>';
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
