<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $touite */
$touite = $touite ?? null;

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function lierTags(string $texte): string
{
    $texte = esc($texte);
    return preg_replace('/#([\p{L}\p{N}_-]+)/u', '<a class="hashtag" href="index.php?route=tags&amp;nom=$1">#$1</a>', $texte) ?? $texte;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Détail du touite</title>
    <link rel="stylesheet" href="CSS/utilisateur.css">
</head>
<body>
<main>
    <?php if ($touite === null): ?>
        <h1>Touite introuvable</h1>
    <?php else: ?>
        <h1>
            Touite de
            <a href="index.php?route=utilisateurs/<?= (int) $touite['id_auteur'] ?>/profil">
                <?= esc((string) $touite['auteur']) ?>
            </a>
        </h1>
        <p><?= nl2br(lierTags((string) $touite['texte'])) ?></p>
        <p><small><?= esc((string) $touite['date_touite']) ?></small></p>
        <p><?= (int) $touite['nombre_likes'] ?> like(s)</p>
        <?php if (($currentUserId ?? false) !== false && ($currentUserId ?? 0) > 0): ?>
            <?php if (!empty($isFollowing)): ?>
                <form class="follow-form" method="post" action="index.php?route=api/utilisateurs/<?= (int) $touite['id_auteur'] ?>/desabonner">
                    <button type="submit">Se désabonner</button>
                    <span role="status"></span>
                </form>
            <?php elseif ((int) $touite['id_auteur'] !== (int) $currentUserId): ?>
                <form class="follow-form" method="post" action="index.php?route=api/utilisateurs/<?= (int) $touite['id_auteur'] ?>/suivre">
                    <button type="submit">Suivre</button>
                    <span role="status"></span>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <a class="button" href="index.php?route=connexion">Se connecter pour suivre</a>
        <?php endif; ?>
        <?php if ((string) ($touite['id_img'] ?? '') !== ''): ?>
            <img src="<?= esc((string) $touite['id_img']) ?>" alt="Image du touite">
        <?php endif; ?>
    <?php endif; ?>
    <p><a class="button" href="index.php?route=touites">Retour aux touites</a></p>
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
</script>
</body>
</html>
