<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $influenceurs */
$influenceurs = $influenceurs ?? [];
$tri = $tri ?? 'DESC';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Métriques des influenceurs</title>
    <link rel="stylesheet" href="CSS/utilisateur.css">
</head>
<body>
<main>
    <nav aria-label="Navigation du back-office">
        <a class="button" href="admin.php">Administration</a>
        <a class="button" href="admin.php?route=tendances">Tendances</a>
        <a class="button" href="admin.php?route=utilisateurs-suivis">Classement des abonnés</a>
        <a class="button" href="admin.php?route=deconnexion">Se déconnecter</a>
    </nav>
    <h1>Métriques des influenceurs</h1>
    <p>Nombre d’abonnés par utilisateur.</p>
    <p>
        Trier :
        <a href="admin.php?route=influenceurs&amp;tri=DESC">Du plus suivi au moins suivi</a> |
        <a href="admin.php?route=influenceurs&amp;tri=ASC">Du moins suivi au plus suivi</a>
        (ordre actuel : <?= $tri === 'ASC' ? 'croissant' : 'décroissant' ?>)
    </p>
    <?php if ($influenceurs === []): ?>
        <p>Aucun utilisateur trouvé.</p>
    <?php else: ?>
        <table class="statistics-table">
            <caption>Nombre d’abonnés par utilisateur</caption>
            <thead>
            <tr><th scope="col">Utilisateur</th><th scope="col">Abonnés</th></tr>
            </thead>
            <tbody>
            <?php foreach ($influenceurs as $influenceur): ?>
                <tr>
                    <th scope="row"><?= htmlspecialchars((string) $influenceur['nom'], ENT_QUOTES, 'UTF-8') ?></th>
                    <td><?= (int) $influenceur['nb_suiveurs'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
