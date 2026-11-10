<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $touites */
$touites = $touites ?? [];

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$authorQuery = $authorId === null ? '' : '&amp;author=' . $authorId;
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
                <p><?= nl2br(e((string) $touite['texte'])) ?></p>
                <small><?= e((string) $touite['date_touite']) ?></small>
                <p><?= (int) $touite['nombre_likes'] ?> like(s)</p>
                <a href="index.php?route=touites/<?= (int) $touite['id_touite'] ?>">Voir le détail</a>
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
</body>
</html>
