<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Analyse statistique</title>
   # <link rel="stylesheet" href="style/style.css" type="text/css" media="screen">#
</head>
<body>
<?php
session_start(); // Démarre la session
require_once 'bd.php'; // Inclut la connexion à la base de données
?>


<div class="navigation">
   <ul>
    <li><a href="exploration.php">Exploration</a></li>
    <li><a href="analyseChoix.php">Statistique</a></li>
    <li><a href="visualisation.php">Visualisation</a></li>
    <li><a href="prediction.php">Prédiction</a></li>
    <li><a href="compte.php">Compte</a></li>
   </ul>
  </div>

<?php
$bdd = getBD();

// Récupérer toutes les caractéristiques (les colonnes de la table sauf la première colonne Id)
$query = $bdd->query("SHOW COLUMNS FROM tumeur");
$caracteristiques = [];
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    if ($row['Field'] != 'Id-tumeur') {
        $caracteristiques[] = $row['Field'];
    }
}
?>


<div class="container">
    <h2>Analyse statistique dédiée aux caractéristiques des tumeurs</h2>
    <p>Choisissez entre 1 et 4 caractéristiques à analyser :</p>
    
    <form method="post" action="analyseRésultat.php">
        <select name="caracteristiques[]" multiple size="10" required>
            <?php foreach ($caracteristiques as $carac): ?>
                <option value="<?php echo htmlspecialchars($carac); ?>"><?php echo htmlspecialchars($carac); ?></option>
            <?php endforeach; ?>
        </select>
        <p>(Maintenez la touche Ctrl ou Cmd pour sélectionner plusieurs options)</p>
        <button type="submit">Soumettre</button>
    </form>
</div>

</body>
</html>