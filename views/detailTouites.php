<?php

declare(strict_types=1);

/** @var array<string, mixed>|null $touite */
$touite = $touite ?? null;

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Détail du touite</title>
    <link rel="stylesheet" href="/CSS/utilisateur.css">
</head>
<body>
<main>
    <?php if ($touite === null): ?>
        <h1>Touite introuvable</h1>
    <?php else: ?>
        <h1>Touite de <?= esc((string) $touite['auteur']) ?></h1>
        <p><?= nl2br(esc((string) $touite['texte'])) ?></p>
        <p><small><?= esc((string) $touite['date_touite']) ?></small></p>
        <p><?= (int) $touite['nombre_likes'] ?> like(s)</p>
        <?php if ((string) ($touite['id_img'] ?? '') !== ''): ?>
            <img src="<?= esc((string) $touite['id_img']) ?>" alt="Image du touite">
        <?php endif; ?>
    <?php endif; ?>
    <p><a class="button" href="index.php?route=touites">Retour aux touites</a></p>
</main>
</body>
</html>
