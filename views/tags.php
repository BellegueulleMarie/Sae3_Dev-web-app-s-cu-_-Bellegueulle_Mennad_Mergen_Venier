<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $tags */
$tags = $tags ?? [];

function tagsEchapper(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Tags</title>
    <link rel="stylesheet" href="CSS/utilisateur.css">
</head>
<body>
<main>
    <h1>Tags</h1>
    <p><a class="button" href="index.php">Accueil</a></p>
    <?php if ($tags === []): ?>
        <p>Aucun tag disponible.</p>
    <?php else: ?>
        <?php foreach ($tags as $tag): ?>
            <article class="tag-card">
                <h2><a href="index.php?route=tags/<?= (int) $tag['id'] ?>">#<?= tagsEchapper((string) $tag['nom']) ?></a></h2>
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
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
<script>
document.querySelectorAll('.tag-subscribe-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const button = form.querySelector('button');
        const status = form.querySelector('[role="status"]');
        button.disabled = true;
        fetch(form.action, {method: 'POST'})
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) throw new Error(data.error || 'Abonnement impossible.');
                    return data;
                });
            })
            .then(function (data) {
                const isUnsubscribe = form.action.includes('desabonner');
                button.textContent = isUnsubscribe ? 'S’abonner' : 'Se désabonner';
                form.action = isUnsubscribe
                    ? form.action.replace('/desabonner', '/abonner')
                    : form.action.replace('/abonner', '/desabonner');
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
