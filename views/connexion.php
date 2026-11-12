<?php

declare(strict_types=1);

/** @var list<array<string, mixed>> $utilisateurs */
$utilisateurs = $utilisateurs ?? [];
$error = htmlspecialchars((string) ($error ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="CSS/utilisateur.css">
</head>
<body>
<main>
    <h1>Accès utilisateur</h1>
    <?php if ($error !== ''): ?>
        <p><strong>Erreur :</strong> <?= $error ?></p>
    <?php endif; ?>

    <?php if ($utilisateurs === []): ?>
        <p>Aucun compte n’existe encore.</p>
    <?php else: ?>
        <form method="post" action="index.php?route=connexion">
            <label for="utilisateur_id">Sélectionner un utilisateur</label>
            <select id="utilisateur_id" name="utilisateur_id" required>
                <option value="">Choisir un utilisateur</option>
                <?php foreach ($utilisateurs as $utilisateur): ?>
                    <option value="<?= (int) $utilisateur['id_util'] ?>">
                        <?= htmlspecialchars((string) $utilisateur['nom'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Se connecter</button>
        </form>
    <?php endif; ?>

    <p>
        <a class="button" href="index.php?route=utilisateurs/creation">Créer un compte</a>
        <a class="button" href="index.php">Accueil</a>
    </p>
</main>
</body>
</html>
