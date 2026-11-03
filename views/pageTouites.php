
<?php

$touites = [
    [
        'auteur' => 'premier',
        'texte' => 'premierTexte',
        'date' => '2026-11-01 10:30:00'
    ],
    [
        'auteur' => 'second',
        'texte' => 'secondTexte',
        'date' => '2026-11-03 09:15:00'
    ],
    [
        'auteur' => 'troisième',
        'texte' => 'troisièmeTexte',
        'date' => '2026-11-02 14:00:00'
    ]
];

// trier les touites du plus récent au plus ancien
usort($touites, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Touiteur - Accueil</title>
</head>

<body>

<h1>Touiteur.app</h1>

<h2>Derniers touites</h2>

<?php

foreach ($touites as $touite) {

    echo "<div>";

    echo "<h3>" . htmlspecialchars($touite['auteur']) . "</h3>";

    echo "<p>" . htmlspecialchars($touite['texte']) . "</p>";

    echo "<p>" . htmlspecialchars($touite['date']) . "</p>";

    echo "</div>";

}

?>

</body>
</html>
