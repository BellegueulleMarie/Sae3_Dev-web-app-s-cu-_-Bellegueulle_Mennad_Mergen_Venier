<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $utilisateurs */
$utilisateurs = $utilisateurs ?? [];

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
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
</body>
</html>
