<?php

declare(strict_types=1);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administration Touiteur</title>
    <link rel="stylesheet" href="CSS/utilisateur.css">
</head>
<body>
<main>
    <h1>Administration Touiteur</h1>
    <p>Consultez les métriques de l’application.</p>
    <nav aria-label="Navigation du back-office">
        <a class="button" href="admin.php?route=influenceurs">Métriques des influenceurs</a>
        <a class="button" href="admin.php?route=tendances">Métriques des tendances</a>
        <a class="button" href="admin.php?route=utilisateurs-suivis">Classement des abonnés</a>
        <a class="button" href="admin.php?route=deconnexion">Se déconnecter</a>
    </nav>
</main>
</body>
</html>
