<?php

$touites = [
        [
                'id' => 1,
                'auteur' => 'toto',
                'texte' => 'premierTexte',
                'date' => '2026-11-01 10:30:00',
                'like' => 12,
                'image' => null
        ],
        [
                'id' => 2,
                'auteur' => 'tata',
                'texte' => 'secondTexte',
                'date' => '2026-11-03 09:15:00',
                'like' => 5,
                'image' => null
        ],
        [
                'id' => 3,
                'auteur' => 'titi',
                'texte' => 'troisièmeTexte',
                'date' => '2026-11-02 14:00:00',
                'like' => 7,
                'image' => null
        ]
];


$touiteTrouve = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    foreach ($touites as $touite) {

        if ($touite['id'] == $id) {
            $touiteTrouve = $touite;
            break;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Détail du touite</title>
</head>

<body>

<h1>Détail du touite</h1>

<?php

if ($touiteTrouve != null) {

    echo "<h2>" . htmlspecialchars($touiteTrouve['auteur']) . "</h2>";
    echo "<p>" . htmlspecialchars($touiteTrouve['texte']) . "</p>";
    echo "<p>Date : " . htmlspecialchars($touiteTrouve['date']) . "</p>";

    echo "<p>Like : " . $touiteTrouve['like'] . "</p>";

    if ($touiteTrouve['image'] != null) {
        echo "<img src='" . htmlspecialchars($touiteTrouve['image']) . "' alt='Image du touite'>";
    }

} else {
    echo "<p>Touite introuvable.</p>";
}

?>

<br><br>
<a href="pagetouites.php">Retour arrière</a>

</body>
</html>