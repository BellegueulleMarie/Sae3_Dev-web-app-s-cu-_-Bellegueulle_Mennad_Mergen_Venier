<?php

declare(strict_types=1);

$error = htmlspecialchars((string) ($error ?? ''), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion administrateur</title>
    <link rel="stylesheet" href="CSS/utilisateur.css">
</head>
<body>
<main>
    <h1>Back-office Touiteur</h1>
    <p>Cette interface est réservée aux administrateurs.</p>
    <?php if ($error !== ''): ?>
        <p role="alert"><?= $error ?></p>
    <?php endif; ?>
    <form method="post" action="admin.php?route=connexion">
        <label for="email">Adresse e-mail
            <input id="email" name="email" type="email" autocomplete="username" value="<?= $email ?>" required>
        </label>
        <label for="password">Mot de passe
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </label>
        <button type="submit">Se connecter</button>
    </form>
</main>
</body>
</html>
