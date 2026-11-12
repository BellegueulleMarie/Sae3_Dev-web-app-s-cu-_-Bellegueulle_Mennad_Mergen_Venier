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

    <form method="POST" action="index.php?route=touites/publier" enctype="multipart/form-data">

        <label for="texte">Votre touite :</label>
        <textarea
            id="texte"
            name="texte"
            maxlength="235"
            required
            placeholder="Quoi de neuf ?"
        ><?= htmlspecialchars($texte ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

        <p>235 caractères maximum.</p>
        <label for="image">Image (facultative) :</label>
        <input
            id="image"
            name="image"
            type="file"
            accept="image/jpeg,image/png,image/gif,image/webp"
        >
        <p>JPEG, PNG, GIF ou WebP, 5 Mo maximum.</p>
        <button type="submit">Publier</button>
    </form>

    <p>
        <a href="index.php?route=touites">Retour aux touites</a>
    </p>

</main>

</body>
</html>
