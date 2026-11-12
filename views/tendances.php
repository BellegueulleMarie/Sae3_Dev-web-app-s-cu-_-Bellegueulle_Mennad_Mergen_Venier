<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $tendances */
$tendances = $tendances ?? [];

/** @var string $tri */
$tri = $tri ?? 'ASC';

function tendancesEchapper(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Les Tendances</title>
    <link rel="stylesheet" href="CSS/utilisateur.css">
</head>
<body>
<main>
    <nav>
        <a class="button" href="index.php">Accueil</a>
        <a class="button" href="index.php?route=tags">Tous les tags</a>
        <a class="button" href="index.php?route=influenceurs">Influenceurs</a>
        <a class="button" href="index.php?route=statistiques">Statistiques</a>
    </nav>

    <h1>Les Tendances</h1>
    <p>Liste des tags les plus mentionnés dans les touites (du plus utilisé au moins utilisé).</p>

    <div style="margin-bottom: 20px;">
        <strong>Tri : </strong>
        <?php if ($tri === 'DESC'): ?>
            <span>Du plus utilisé au moins utilisé (par défaut)</span> |
            <a href="index.php?route=tendances&amp;tri=ASC">Passer en ordre croissant</a>
        <?php else: ?>
            <a href="index.php?route=tendances&amp;tri=DESC">Passer en ordre décroissant (du plus utilisé au moins utilisé)</a> |
            <span>Du moins utilisé au plus utilisé</span>
        <?php endif; ?>
    </div>

    <?php if ($tendances === []): ?>
        <p>Aucun tag enregistré pour le moment.</p>
    <?php else: ?>
        <?php foreach ($tendances as $index => $tag): ?>
            <article>
                <h2>
                    #<?= $index + 1 ?> - 
                    <a class="hashtag" href="index.php?route=tags&amp;nom=<?= urlencode((string) $tag['nom']) ?>">
                        #<?= tendancesEchapper((string) $tag['nom']) ?>
                    </a>
                </h2>
                <p><strong>Mentions : </strong><?= (int) ($tag['nb_mentions'] ?? 0) ?> fois</p>
                <a class="button" href="index.php?route=tags&amp;nom=<?= urlencode((string) $tag['nom']) ?>">
                    Voir les touites avec ce tag
                </a>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
</body>
</html>
