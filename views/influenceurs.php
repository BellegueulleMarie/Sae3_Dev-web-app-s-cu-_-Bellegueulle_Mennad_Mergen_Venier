<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $influenceurs */
$influenceurs = $influenceurs ?? [];

/** @var string $tri */
$tri = $tri ?? 'ASC';

$currentUserId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

function influenceursEchapper(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Les Influenceurs</title>
    <link rel="stylesheet" href="CSS/utilisateur.css">
</head>
<body>
<main>
    <nav>
        <a class="button" href="index.php">Accueil</a>
        <a class="button" href="index.php?route=utilisateurs">Tous les utilisateurs</a>
        <a class="button" href="index.php?route=tendances">Tendances</a>
        <a class="button" href="index.php?route=statistiques">Statistiques</a>
    </nav>

    <h1>Les Influenceurs</h1>
    <p>Liste des utilisateurs les plus suivis sur Touiteur (ordonnée par nombre de suiveurs).</p>

    <div style="margin-bottom: 20px;">
        <strong>Tri : </strong>
        <?php if ($tri === 'ASC'): ?>
            <span>Ordre croissant (spécification SAE)</span> |
            <a href="index.php?route=influenceurs&amp;tri=DESC">Passer en ordre décroissant</a>
        <?php else: ?>
            <a href="index.php?route=influenceurs&amp;tri=ASC">Passer en ordre croissant (spécification SAE)</a> |
            <span>Ordre décroissant</span>
        <?php endif; ?>
    </div>

    <?php if ($influenceurs === []): ?>
        <p>Aucun utilisateur trouvé.</p>
    <?php else: ?>
        <?php foreach ($influenceurs as $index => $influenceur): ?>
            <article>
                <h2>
                    #<?= $index + 1 ?> - <?= influenceursEchapper((string) $influenceur['nom']) ?>
                </h2>
                <p><strong>Abonnés : </strong><?= (int) ($influenceur['nb_suiveurs'] ?? 0) ?> suiveur(s)</p>
                <p><?= influenceursEchapper((string) $influenceur['email']) ?></p>
                <a class="button" href="index.php?route=utilisateurs/<?= (int) $influenceur['id_util'] ?>/profil">
                    Voir le profil
                </a>

                <?php if ($currentUserId !== false && $currentUserId > 0 && (int) $influenceur['id_util'] !== (int) $currentUserId): ?>
                    <?php if (!empty($influenceur['is_following'])): ?>
                        <form class="follow-form" method="post"
                              action="index.php?route=api/utilisateurs/<?= (int) $influenceur['id_util'] ?>/desabonner">
                            <button type="submit">Se désabonner</button>
                            <span class="follow-message" role="status"></span>
                        </form>
                    <?php else: ?>
                        <form class="follow-form" method="post"
                              action="index.php?route=api/utilisateurs/<?= (int) $influenceur['id_util'] ?>/suivre">
                            <button type="submit">Suivre</button>
                            <span class="follow-message" role="status"></span>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
<script>
document.querySelectorAll('.follow-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const button = form.querySelector('button');
        const message = form.querySelector('.follow-message');
        button.disabled = true;
        fetch(form.action, {method: 'POST', headers: {'Accept': 'application/json'}})
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.error || 'Impossible de suivre cet utilisateur.');
                    }
                    return data;
                });
            })
            .then(function (data) {
                const isUnfollow = form.action.includes('desabonner');
                button.textContent = isUnfollow ? 'Suivre' : 'Se désabonner';
                form.action = isUnfollow
                    ? form.action.replace('/desabonner', '/suivre')
                    : form.action.replace('/suivre', '/desabonner');
                message.textContent = data.message;
            })
            .catch(function (error) {
                button.disabled = false;
                message.textContent = error.message;
            });
    });
});
</script>
</body>
</html>
