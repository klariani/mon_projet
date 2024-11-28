<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats de l'analyse</title>
   #<link rel="stylesheet" href="style/style.css" type="text/css" media="screen">
</head>
<body>
</head>
<body>
<?php
session_start();
require_once 'bd.php';
?>
<header>
    <nav>
        <a href="#">Exploration</a>
        <a href="analyseChoix.php">Statistique</a>
        <a href="#">Visualisation</a>
        <a href="#">Prédiction</a>
        <a href="#">Compte</a>
    </nav>
</header>



<?php
if (!isset($_POST['caracteristiques']) || count($_POST['caracteristiques']) < 1 || count($_POST['caracteristiques']) > 4) {
    echo "<p>Veuillez choisir entre 1 et 4 caractéristiques.</p>";
    echo '<a href="analyseChoix.php">Retour</a>';
    exit;
}

$caracteristiques = $_POST['caracteristiques'];
$bdd = getBD();

// Préparer la requête SQL dynamiquement
$query = "SELECT " . implode(", ", $caracteristiques) . " FROM tumeur";
$stmt = $bdd->prepare($query);
$stmt->execute();

echo "<h2>Résultats de l'analyse</h2>";
echo "<table border='1'><tr>";
foreach ($caracteristiques as $carac) {
    echo "<th>" . htmlspecialchars($carac) . "</th>";
}
echo "</tr>";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    foreach ($caracteristiques as $carac) {
        echo "<td>" . htmlspecialchars($row[$carac]) . "</td>";
    }
    echo "</tr>";
}
echo "</table>";
?>

<a href="analyseChoix.php">Retour</a>

</body>
</html>
