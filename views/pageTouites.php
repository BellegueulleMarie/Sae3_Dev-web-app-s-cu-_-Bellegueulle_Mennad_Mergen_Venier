<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $touites */
$touites = $touites ?? [];

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function lierTags(string $texte): string
{
    $texte = e($texte);
    return preg_replace('/#([\p{L}\p{N}_-]+)/u', '<a class="hashtag" href="index.php?route=tags&amp;nom=$1">#$1</a>', $texte) ?? $texte;
}

$authorQuery = $authorId === null ? '' : '&amp;author=' . $authorId;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Les touites</title>
    <link rel="stylesheet" href="CSS/utilisateur.css">
</head>
<body>
<main>
    <h1><?= $authorId === null ? 'Les derniers touites' : 'Les touites de cet auteur' ?></h1>
    <p><a class="button" href="index.php">Accueil</a></p>
    <?php if ($authorId !== null): ?>
        <p><a class="button" href="index.php?route=touites">Voir tous les touites</a></p>
    <?php endif; ?>
    <p>
        <a class="button" href="index.php?route=touites/nouveau">
            Écrire un touite
        </a>
    </p>
    <?php if ($touites === []): ?>
        <p>Aucun touite enregistré.</p>
    <?php else: ?>
        <?php foreach ($touites as $touite): ?>
            <article>
                <h2>
                    <a href="index.php?route=touites&amp;author=<?= (int) $touite['id_auteur'] ?>">
                        <?= e((string) $touite['auteur']) ?>
                    </a>
                </h2>
                <p><?= nl2br(lierTags((string) $touite['texte'])) ?></p>
                <small><?= e((string) $touite['date_touite']) ?></small>
                <div class="vote-container">
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <form class="vote-form" method="post" action="index.php?route=api/touites/<?= (int) $touite['id_touite'] ?>/evaluer">
                            <button type="submit" name="valeur" value="1" class="vote-button vote-like" title="J'aime">
                                ♥ +1
                            </button>
                            <button type="submit" name="valeur" value="-1" class="vote-button vote-dislike" title="Je n'aime pas">
                                💔 -1
                            </button>
                            <span class="vote-score"><?= (int) ($touite['nombre_likes'] ?? 0) ?></span>
                        </form>
                    <?php else: ?>
                        <span>♡ <?= (int) ($touite['nombre_likes'] ?? 0) ?></span>
                        <a href="index.php?route=connexion">Connectez-vous pour voter</a>
                    <?php endif; ?>
                    <span class="vote-status" role="status"></span>
                </div>
                <p>
                    <a class="button button-detail" href="index.php?route=touites/<?= (int) $touite['id_touite'] ?>">
                        Voir le détail
                    </a>
                </p>
            </article>
        <?php endforeach; ?>
    <?php
    // Variables attendues : $page, $totalPages
    $page = $page ?? 1;
    $totalPages = $totalPages ?? 1;
    ?>


    <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php if ($page > 1): ?>
                <a class="button" href="index.php?route=touites&amp;page=<?= $page - 1 ?><?= $authorQuery ?>">Page précédente</a>
            <?php endif; ?>

            <span>Page <?= $page ?> sur <?= $totalPages ?></span>

            <?php if ($page < $totalPages): ?>
                <a class="button" href="index.php?route=touites&amp;page=<?= $page + 1 ?><?= $authorQuery ?>">Page suivante</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
    <?php endif; ?>
</main>
<script>
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
