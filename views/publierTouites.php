<?php
declare(strict_types=1);
?>

<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Publier un touite</title>
    <link rel="stylesheet" href="/CSS/utilisateur.css">
</head>

<body>

<main>

    <h1>Écrire un touite</h1>

    <?php if (($error ?? '') !== ''): ?>
        <p role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="POST" action="index.php?route=touites/publier">

        <label for="texte">Votre touite :</label>
        <textarea
            id="texte"
            name="texte"
            maxlength="235"
            required
            placeholder="Quoi de neuf ?"
        ></textarea>

        <p>235 caractères maximum.</p>
        <button type="submit">Publier</button>
    </form>

    <p>
        <a href="index.php?route=touites">Retour aux touites</a>
    </p>

</main>

</body>
</html>
