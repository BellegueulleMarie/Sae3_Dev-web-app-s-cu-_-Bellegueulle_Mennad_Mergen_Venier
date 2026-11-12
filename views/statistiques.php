<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $statistics */
$statistics = $statistics ?? [];
$utilisateur = $utilisateur ?? null;
$followers = $followers ?? [];
$scoreMoyen = $scoreMoyen ?? 0.0;

function statistiquesEchapper(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statistiques</title>
    <link rel="stylesheet" href="/CSS/utilisateur.css">
</head>
<body>
<main>
    <h1>Statistiques</h1>
    <p>
        <a class="button" href="index.php">Accueil</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a class="button" href="index.php?route=utilisateurs/<?= (int) $_SESSION['user_id'] ?>/profil">Mon profil</a>
        <?php endif; ?>
        <a class="button" href="index.php?route=tags">Tags</a>
    </p>

    <!-- Partie 12 : L'utilisateur narcissique -->
    <section>
        <h2>Statistiques narcissiques</h2>

        <?php if ($utilisateur !== null): ?>
            <p>Voici vos statistiques personnelles et les personnes qui vous suivent :</p>
            <div class="stat-box">
                <p><strong>Score moyen de mes touites :</strong> <?= number_format((float) $scoreMoyen, 2, ',', ' ') ?> like(s) en moyenne</p>
                <p><strong>Nombre de personnes qui me suivent :</strong> <?= count($followers) ?></p>
            </div>

            <h3>Utilisateurs qui me suivent</h3>
            <?php if ($followers === []): ?>
                <p>Aucun utilisateur ne vous suit pour le moment.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($followers as $follower): ?>
                        <li>
                            <a href="index.php?route=utilisateurs/<?= (int) $follower['id_util'] ?>/profil">
                                <?= statistiquesEchapper((string) $follower['nom']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php else: ?>
            <p>Vous devez être connecté pour consulter vos statistiques narcissiques.</p>
            <p><a class="button" href="index.php?route=connexion">Se connecter</a></p>
        <?php endif; ?>
    </section>

    <!-- Statistiques par sujet (tags) -->
    <section>
        <h2>Statistiques par sujet</h2>
        <p>Les statistiques utilisent uniquement les likes déjà enregistrés.</p>

        <?php if ($statistics === []): ?>
            <p>Aucun sujet disponible.</p>
        <?php else: ?>
            <table class="statistics-table">
                <caption>Engagement par sujet</caption>
                <thead>
                <tr>
                    <th scope="col">Sujet</th>
                    <th scope="col">Publications</th>
                    <th scope="col">Likes</th>
                    <th scope="col">Moyenne de likes</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($statistics as $statistic): ?>
                    <tr>
                        <th scope="row">#<?= statistiquesEchapper((string) $statistic['nom']) ?></th>
                        <td><?= (int) $statistic['nombre_touites'] ?></td>
                        <td><?= (int) $statistic['total_likes'] ?></td>
                        <td><?= number_format((float) $statistic['moyenne_likes'], 2, ',', ' ') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
