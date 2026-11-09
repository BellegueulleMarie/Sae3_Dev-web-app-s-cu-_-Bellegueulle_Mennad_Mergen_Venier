<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $utilisateurs */
$utilisateurs = $utilisateurs ?? [];
$currentUserId = $currentUserId ?? null;

function utilisateursEchapper(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Utilisateurs</title>
    <link rel="stylesheet" href="/CSS/utilisateur.css">
</head>
<body>
<main>
    <h1>Les utilisateurs</h1>
    <p>
        <a class="button" href="index.php">Accueil</a>
        <a class="button" href="index.php?route=utilisateurs/creation">Créer un utilisateur</a>
    </p>

    <?php if ($utilisateurs === []): ?>
        <p>Aucun utilisateur enregistré.</p>
    <?php else: ?>
        <?php foreach ($utilisateurs as $utilisateur): ?>
            <article>
                <h2><?= utilisateursEchapper((string) $utilisateur['nom']) ?></h2>
                <p><?= utilisateursEchapper((string) $utilisateur['email']) ?></p>
                <p><?= (int) $utilisateur['nb_touites'] ?> touite(s)</p>
                <a class="button" href="index.php?route=utilisateurs/<?= (int) $utilisateur['id_util'] ?>/profil">
                    Voir le profil
                </a>
                <?php if ($currentUserId !== null && (int) $utilisateur['id_util'] !== (int) $currentUserId): ?>
                    <?php if (!empty($utilisateur['is_following'])): ?>
                        <button type="button" disabled>Suivi</button>
                    <?php else: ?>
                        <form class="follow-form" method="post"
                              action="index.php?route=api/utilisateurs/<?= (int) $utilisateur['id_util'] ?>/suivre">
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
                button.textContent = 'Suivi';
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
