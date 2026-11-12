<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $utilisateurs */
$utilisateurs = $utilisateurs ?? [];
$estAdministrateur = $estAdministrateur ?? false;

function adminUtilisateursSuivisEchapper(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Utilisateurs les plus suivis</title>
    <link rel="stylesheet" href="CSS/utilisateur.css">
</head>
<body>
<main>
    <h1>Utilisateurs les plus suivis</h1>
    <p><a class="button" href="index.php">Accueil</a></p>

    <?php if (!$estAdministrateur): ?>
        <p role="alert">Cette page est réservée aux administrateurs.</p>
    <?php elseif ($utilisateurs === []): ?>
        <p>Aucun utilisateur n’est enregistré.</p>
    <?php else: ?>
        <table class="statistics-table">
            <caption>Nombre de suiveurs par utilisateur, du plus petit au plus grand</caption>
            <thead>
            <tr>
                <th scope="col">Utilisateur</th>
                <th scope="col">Nombre de suiveurs</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($utilisateurs as $utilisateur): ?>
                <tr>
                    <th scope="row"><?= adminUtilisateursSuivisEchapper((string) $utilisateur['nom']) ?></th>
                    <td><?= (int) $utilisateur['nb_suiveurs'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>
