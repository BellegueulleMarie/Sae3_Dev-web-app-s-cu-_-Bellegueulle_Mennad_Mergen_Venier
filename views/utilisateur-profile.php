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
    <link rel="stylesheet" href="/CSS/utilisateur.css">
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
        <p><strong>Nombre de suiveurs :</strong> <?= (int) $utilisateur['followers_count'] ?></p>
        <?php if ((int) $utilisateur['id_util'] !== (int) ($_SESSION['user_id'] ?? 0)): ?>
            <?php if (!empty($isFollowing)): ?>
                <button type="button" disabled>Suivi</button>
            <?php else: ?>
                <form method="post" action="index.php?route=api/utilisateurs/<?= (int) $utilisateur['id_util'] ?>/suivre">
                    <button type="submit">Suivre</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
        <h2>Touites de <?= profilEchapper((string) $utilisateur['nom']) ?></h2>
        <?php if (($touites ?? []) === []): ?>
            <p>Cet utilisateur n’a publié aucune touite.</p>
        <?php else: ?>
            <?php foreach ($touites as $touite): ?>
                <article>
                    <p><?= nl2br(profilEchapper((string) $touite['texte'])) ?></p>
                    <small><?= profilEchapper((string) $touite['date_touite']) ?></small>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
    <p>
        <a class="button" href="index.php?route=touites">Voir les touites</a>
        <a class="button" href="index.php">Accueil</a>
    </p>
</main>
</body>
</html>
