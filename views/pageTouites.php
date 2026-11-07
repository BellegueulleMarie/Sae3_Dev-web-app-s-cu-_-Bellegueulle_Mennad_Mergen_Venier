<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $touites */
$touites = $touites ?? [];

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Les touites</title>
    <link rel="stylesheet" href="/CSS/utilisateur.css">
</head>
<body>
<main>
    <h1>Les derniers touites</h1>
    <p><a class="button" href="index.php">Accueil</a></p>
    <?php if ($touites === []): ?>
        <p>Aucun touite enregistré.</p>
    <?php else: ?>
        <?php foreach ($touites as $touite): ?>
            <article>
                <h2>
                    <a href="index.php?route=utilisateurs/<?= (int) $touite['id_auteur'] ?>/profil">
                        <?= e((string) $touite['auteur']) ?>
                    </a>
                </h2>
                <p><?= nl2br(e((string) $touite['texte'])) ?></p>
                <small><?= e((string) $touite['date_touite']) ?></small>
                <p><?= (int) $touite['nombre_likes'] ?> like(s)</p>
                <a href="index.php?route=touites/<?= (int) $touite['id_touite'] ?>">Voir le détail</a>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
</body>
</html>
