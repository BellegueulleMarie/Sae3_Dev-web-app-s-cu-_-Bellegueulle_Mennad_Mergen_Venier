<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $tendances */
$tendances = $tendances ?? [];
$tri = $tri ?? 'DESC';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Métriques des tendances</title>
    <link rel="stylesheet" href="CSS/utilisateur.css">
</head>
<body>
<main>
    <nav aria-label="Navigation du back-office">
        <a class="button" href="admin.php">Administration</a>
        <a class="button" href="admin.php?route=influenceurs">Influenceurs</a>
        <a class="button" href="admin.php?route=utilisateurs-suivis">Classement des abonnés</a>
        <a class="button" href="admin.php?route=deconnexion">Se déconnecter</a>
    </nav>
    <h1>Métriques des tendances</h1>
    <p>Nombre de mentions par tag dans les touites.</p>
    <p>
        Trier :
        <a href="admin.php?route=tendances&amp;tri=DESC">Du plus utilisé au moins utilisé</a> |
        <a href="admin.php?route=tendances&amp;tri=ASC">Du moins utilisé au plus utilisé</a>
        (ordre actuel : <?= $tri === 'ASC' ? 'croissant' : 'décroissant' ?>)
    </p>
    <?php if ($tendances === []): ?>
        <p>Aucun tag enregistré.</p>
    <?php else: ?>
        <table class="statistics-table">
            <caption>Nombre de mentions par tag</caption>
            <thead>
            <tr><th scope="col">Tag</th><th scope="col">Mentions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($tendances as $tag): ?>
                <tr>
                    <th scope="row">#<?= htmlspecialchars((string) $tag['nom'], ENT_QUOTES, 'UTF-8') ?></th>
                    <td><?= (int) $tag['nb_mentions'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
