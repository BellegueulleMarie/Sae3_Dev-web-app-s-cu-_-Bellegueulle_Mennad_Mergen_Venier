<?php


// j'initialise la variable touites
$touites = [
    [
        'auteur' => 'toto',
        'texte' => 'premierTexte',
        'date' => '2026-11-01 10:30:00',
        'like' => 12,
        'image' => null
    ],
    [
        'auteur' => 'tata',
        'texte' => 'secondTexte',
        'date' => '2026-11-03 09:15:00',
        'like' => 5,
        'image' => null
    ],
    [
        'auteur' => 'titi',
        'texte' => 'troisièmeTexte',
        'date' => '2026-11-02 14:00:00',
        'like' => 7,
        'image' => null
    ]
];


/*
 *trier les touites du plus récent au plus ancien
 */
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

/*
 * foreach parcours une liste élément par élément
 * $touites : liste des touites récupérés par le Controller
 * as $touite : chaque élément de la liste sera temporairement appelé $touite
 */
foreach ($touites as $touite) {

    /*
     * ajout de href pour que quand on clique sur le premier touite on ouvre la page detailTouite
    */
    echo "<a href='detailTouite.php?id=" . $touite['id'] . "'>";

    echo "<div>";

    echo "<h3>" . htmlspecialchars($touite['auteur']) . "</h3>";
    echo "<p>" . htmlspecialchars($touite['texte']) . "</p>";
    echo "<p>" . htmlspecialchars($touite['date']) . "</p>";
    echo "<p>Like : " . $touite['Like'] . "</p>";

    echo "</div>";

}

?>

</body>
</html>
