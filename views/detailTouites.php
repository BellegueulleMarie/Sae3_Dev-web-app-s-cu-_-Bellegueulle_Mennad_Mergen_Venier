<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $touite */
$touite = $touite ?? null;

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Détail du touite</title>
    <link rel="stylesheet" href="/CSS/utilisateur.css">
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
        <p><?= nl2br(esc((string) $touite['texte'])) ?></p>
        <p><small><?= esc((string) $touite['date_touite']) ?></small></p>
        <p><?= (int) $touite['nombre_likes'] ?> like(s)</p>
        <?php if (($currentUserId ?? false) !== false && ($currentUserId ?? 0) > 0): ?>
            <?php if (!empty($isFollowing)): ?>
                <button type="button" disabled>Suivi</button>
            <?php elseif ((int) $touite['id_auteur'] !== (int) $currentUserId): ?>
                <form method="post" action="index.php?route=api/utilisateurs/<?= (int) $touite['id_auteur'] ?>/suivre">
                    <button type="submit">Suivre</button>
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
</body>
</html>
