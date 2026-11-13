<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $utilisateur */
$utilisateur = $utilisateur ?? null;

function profilEchapper(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Profil utilisateur</title>
    <link rel="stylesheet" href="CSS/utilisateur.css">
</head>
<body>
<main>
    <?php if ($utilisateur === null): ?>
        <h1>Utilisateur introuvable</h1>
    <?php else: ?>
        <h1>Profil de <?= profilEchapper((string) $utilisateur['nom']) ?></h1>
        <p><strong>ID sélectionné :</strong> <?= (int) $utilisateur['id_util'] ?></p>
        <p><strong>Email :</strong> <?= profilEchapper((string) $utilisateur['email']) ?></p>
        <p><strong>Nombre de touites :</strong> <?= (int) $utilisateur['nb_touites'] ?></p>
        <p><strong>Nombre de suiveurs :</strong> <?= count($followers ?? []) ?></p>
        <p><strong>Score moyen des touites :</strong> <?= number_format((float) ($scoreMoyen ?? 0.0), 2, ',', ' ') ?></p>
        <?php if ((int) $utilisateur['id_util'] !== (int) ($_SESSION['user_id'] ?? 0)): ?>
            <?php if (!empty($isFollowing)): ?>
                <form class="follow-form" method="post" action="index.php?route=api/utilisateurs/<?= (int) $utilisateur['id_util'] ?>/desabonner">
                    <button type="submit">Se désabonner</button>
                    <span role="status"></span>
                </form>
            <?php else: ?>
                <form class="follow-form" method="post" action="index.php?route=api/utilisateurs/<?= (int) $utilisateur['id_util'] ?>/suivre">
                    <button type="submit">Suivre</button>
                    <span role="status"></span>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <h2>Utilisateurs qui le suivent</h2>
        <?php if (($followers ?? []) === []): ?>
            <p>Aucun utilisateur ne suit cette personne pour l’instant.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($followers as $follower): ?>
                    <li>
                        <a href="index.php?route=utilisateurs/<?= (int) $follower['id_util'] ?>/profil">
                            <?= profilEchapper((string) $follower['nom']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <h2>Touites de <?= profilEchapper((string) $utilisateur['nom']) ?></h2>
        <?php if (($touites ?? []) === []): ?>
            <p>Cet utilisateur n’a publié aucune touite.</p>
        <?php else: ?>
            <?php foreach ($touites as $touite): ?>
                <article class="touite-card">
                    <p><?= nl2br(profilEchapper((string) $touite['texte'])) ?></p>
                    <small><?= profilEchapper((string) $touite['date_touite']) ?></small>
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
    <?php endif; ?>
    <p>
        <a class="button" href="index.php?route=touites">Voir les touites</a>
        <a class="button" href="index.php?route=statistiques">Statistiques</a>
        <a class="button" href="index.php">Accueil</a>
    </p>
</main>
<script>
document.querySelectorAll('.follow-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const button = form.querySelector('button');
        const status = form.querySelector('[role="status"]');
        button.disabled = true;
        fetch(form.action, {method: 'POST', headers: {'Accept': 'application/json'}})
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) throw new Error(data.error || 'Impossible de suivre cet utilisateur.');
                    return data;
                });
            })
            .then(function (data) {
                const isUnfollow = form.action.includes('desabonner');
                button.textContent = isUnfollow ? 'Suivre' : 'Se désabonner';
                form.action = isUnfollow
                    ? form.action.replace('/desabonner', '/suivre')
                    : form.action.replace('/suivre', '/desabonner');
                status.textContent = data.message;
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
