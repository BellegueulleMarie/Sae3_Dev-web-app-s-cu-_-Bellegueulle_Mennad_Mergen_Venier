<?php

include 'donneesTouites.php';

$id = $_GET['id'];

$touiteTrouve = null;

foreach ($touites as $touite) {

    if ($touite['id'] == $id) {
        $touiteTrouve = $touite;
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
    echo "<p>Like : " . $touiteTrouve['Like'] . "</p>";

    if ($touiteTrouve['image'] != null) {
        echo "<img src='" . htmlspecialchars($touiteTrouve['image']) . "' alt='Image du touite'>";
    }

} else {

    echo "<p>Touite introuvable.</p>";

}

?>

<a href="pageTouites.php">Retour à l'accueil</a>

</body>
</html>