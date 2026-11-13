<?php

declare(strict_types=1);

function pageAccueil(string $message, array $touites): void
{
    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    $isConnected = $userId !== false && $userId > 0;
    $isAdmin = $isConnected && ($_SESSION['role'] ?? null) === 'admin';
    $navigation = $isConnected
        ? '<a class="profile-avatar" href="index.php?route=utilisateurs/' . (int) $userId .
            '/profil" aria-label="Voir mon profil">U</a>' .
          '<a class="button" href="index.php?route=utilisateurs/' . (int) $userId .
            '/profil">Mon profil</a>' .
          '<a class="button" href="index.php?route=utilisateurs">Utilisateurs</a>' .
          '<a class="button" href="index.php?route=tags">Gérer les tags</a>' .
          '<a class="button" href="index.php?route=statistiques">Statistiques</a>' .
          ($isAdmin
              ? '<a class="button" href="admin.php">Administration</a>' .
                '<a class="button" href="admin.php?route=utilisateurs-suivis">Suiveurs</a>'
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
            <link rel="stylesheet" href="CSS/utilisateur.css">
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
            // Gestion AJAX des votes (Like / Dislike)
            document.querySelectorAll('.vote-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const submitter = event.submitter;
                    const valeur = (submitter && submitter.value) ? submitter.value : '1';
                    const buttons = form.querySelectorAll('.vote-button');
                    const score = form.querySelector('.vote-score');
                    const container = form.closest('.vote-container') || form.parentElement;
                    const status = container ? container.querySelector('.vote-status') : null;

                    buttons.forEach(function (btn) {
                        btn.disabled = true;
                    });

                    const formData = new FormData(form);
                    formData.set('valeur', valeur);

                    fetch(form.action, {
                        method: 'POST',
                        headers: {'Accept': 'application/json'},
                        body: formData
                    })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            if (!response.ok) {
                                throw new Error(data.error || 'Erreur lors du vote.');
                            }
                            return data;
                        });
                    })
                    .then(function (data) {
                        if (score) score.textContent = data.score;
                        if (submitter) {
                            submitter.classList.add(data.valeur > 0 ? 'is-liked' : 'is-disliked');
                        }
                        if (status) status.textContent = data.message;
                    })
                    .catch(function (error) {
                        if (status) status.textContent = error.message;
                        if (!error.message.includes('déjà voté')) {
                            buttons.forEach(function (btn) {
                                btn.disabled = false;
                            });
                        }
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
    $touiteId = (int) ($touite['id_touite'] ?? 0);
    $score = (int) ($touite['nombre_likes'] ?? 0);
    $auteurHtml = $auteurId > 0
        ? '<a href="index.php?route=utilisateurs/' . $auteurId . '/profil">' . $auteur . '</a>'
        : $auteur;
    $texte = afficherTags((string) ($touite['texte'] ?? ''));
    $date = echapper((string) ($touite['date_touite'] ?? ''));

    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    $isConnected = $userId !== false && $userId > 0;

    $voteHtml = '';
    if ($isConnected) {
        $voteHtml = <<<HTML
            <div class="vote-container">
                <form class="vote-form" method="post" action="index.php?route=api/touites/{$touiteId}/evaluer">
                    <button type="submit" name="valeur" value="1" class="vote-button vote-like" title="J'aime">
                        ♥ +1
                    </button>
                    <button type="submit" name="valeur" value="-1" class="vote-button vote-dislike" title="Je n'aime pas">
                        💔 -1
                    </button>
                    <span class="vote-score">{$score}</span>
                </form>
                <span class="vote-status" role="status"></span>
            </div>
HTML;
    } else {
        $voteHtml = <<<HTML
            <div class="vote-container">
                <span>♡ {$score}</span>
                <a href="index.php?route=connexion">Connectez-vous pour voter</a>
            </div>
HTML;
    }

    echo <<<HTML
        <article class="touite-card">
            <header>
                <strong aria-label="Auteur de la touite">$auteurHtml</strong>
            </header>
            <p>$texte</p>
            <small>$date</small>
            $voteHtml
            <p><a class="button button-detail" href="index.php?route=touites/{$touiteId}">Voir le détail</a></p>
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
