<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $tags */
$tags = $tags ?? [];
/** @var array<string, mixed>|null $tagSelectionne */
$tagSelectionne = $tagSelectionne ?? null;
/** @var list<array<string, mixed>> $touites */
$touites = $touites ?? [];

function tagsEchapper(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $tagSelectionne !== null ? '#' . tagsEchapper((string) $tagSelectionne['nom']) . ' - Touiteur' : 'Tags' ?></title>
    <link rel="stylesheet" href="CSS/utilisateur.css">
</head>
<body>
<main>
    <?php if ($tagSelectionne !== null): ?>
        <!-- Vue détaillée d'un tag avec ses touites -->
        <h1>Tag #<?= tagsEchapper((string) $tagSelectionne['nom']) ?></h1>
        <p>
            <a class="button" href="index.php">Accueil</a>
            <a class="button" href="index.php?route=tags">Tous les tags</a>
            <a class="button" href="index.php?route=tendances">Tendances</a>
            <a class="button" href="index.php?route=statistiques">Statistiques</a>
        </p>

        <section class="stat-box">
            <h2>Gérer votre abonnement</h2>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <?php if (!empty($tagSelectionne['is_subscribed'])): ?>
                    <form class="tag-subscribe-form" method="post"
                          action="index.php?route=api/tags/<?= (int) $tagSelectionne['id'] ?>/desabonner">
                        <button type="submit">Se désabonner</button>
                        <span class="action-status" role="status"></span>
                    </form>
                <?php else: ?>
                    <form class="tag-subscribe-form" method="post"
                          action="index.php?route=api/tags/<?= (int) $tagSelectionne['id'] ?>/abonner">
                        <button type="submit">S’abonner</button>
                        <span class="action-status" role="status"></span>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <p>Connectez-vous pour vous abonner à ce tag.</p>
                <a class="button" href="index.php?route=connexion">Se connecter</a>
            <?php endif; ?>
        </section>

        <h2>Touites associés (<?= count($touites) ?>)</h2>
        <?php if ($touites === []): ?>
            <p>Aucun touite n’est associé à ce tag pour le moment.</p>
        <?php else: ?>
            <?php foreach ($touites as $touite): ?>
                <article>
                    <header>
                        <a href="index.php?route=utilisateurs/<?= (int) $touite['id_auteur'] ?>/profil">
                            <strong><?= tagsEchapper((string) $touite['auteur']) ?></strong>
                        </a>
                    </header>
                    <p><?= nl2br(tagsEchapper((string) $touite['texte'])) ?></p>
                    <small><?= tagsEchapper((string) $touite['date_touite']) ?></small>
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
        <?php endif; ?>

    <?php else: ?>
        <!-- Liste générale des tags -->
        <h1>Tags</h1>
        <p>
            <a class="button" href="index.php">Accueil</a>
            <a class="button" href="index.php?route=tendances">Voir les tendances</a>
            <a class="button" href="index.php?route=statistiques">Statistiques</a>
        </p>

        <?php if ($tags === []): ?>
            <p>Aucun tag disponible.</p>
        <?php else: ?>
            <?php foreach ($tags as $tag): ?>
                <article class="tag-card">
                    <h2><a href="index.php?route=tags/<?= (int) $tag['id'] ?>">#<?= tagsEchapper((string) $tag['nom']) ?></a></h2>
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <?php if (!empty($tag['is_subscribed'])): ?>
                            <form class="tag-subscribe-form" method="post"
                                  action="index.php?route=api/tags/<?= (int) $tag['id'] ?>/desabonner">
                                <button type="submit">Se désabonner</button>
                                <span class="action-status" role="status"></span>
                            </form>
                        <?php else: ?>
                            <form class="tag-subscribe-form" method="post"
                                  action="index.php?route=api/tags/<?= (int) $tag['id'] ?>/abonner">
                                <button type="submit">S’abonner</button>
                                <span class="action-status" role="status"></span>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</main>

<script>
document.querySelectorAll('.tag-subscribe-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const button = form.querySelector('button');
        const status = form.querySelector('[role="status"]');
        button.disabled = true;

        fetch(form.action, {
            method: 'POST',
            headers: {'Accept': 'application/json'}
        })
        .then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) throw new Error(data.error || 'Erreur lors de l’opération.');
                return data;
            });
        })
        .then(function (data) {
            const isUnsubscribe = form.action.includes('desabonner');
            button.textContent = isUnsubscribe ? 'S’abonner' : 'Se désabonner';
            form.action = isUnsubscribe
                ? form.action.replace('desabonner', 'abonner')
                : form.action.replace('abonner', 'desabonner');
            status.textContent = data.message;
            button.disabled = false;
        })
        .catch(function (error) {
            button.disabled = false;
            status.textContent = error.message;
        });
    });
});

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
