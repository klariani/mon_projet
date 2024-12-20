<?php

require "./getBd.php";
$bdd = getBD();
session_start();

$query = $bdd->prepare("SELECT tumeur.`Id-tumeur`, diagnostique.libelle_diagnostic FROM tumeur JOIN diagnostic ON tumeur.`Id-tumeur` = diagnostic.`Id-tumeur` JOIN diagnostique ON diagnostique.code_diagnostic = diagnostic.code_diagnostic;");
$query->execute();
$diagnostics = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/css2?family=Kaisei+HarunoUmi&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./style/train.css" type="text/css" media="screen" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title> Visualisation </title>
</head>
<body>
    <div class="navigation">
        <ul>
            <li><a href="exploration.php">Exploration</a></li>
            <li><a href="analyseChoix.php">Statistique</a></li>
            <li><a href="visualisation.php">Visualisation</a></li>
            <li><a href="prediction.html">Prédiction</a></li>
            <li><a href="login.php">Compte</a></li>
        </ul>
    </div>
    <img src="./image/Capture d'écran 2024-10-26 081223.png">
    <div id="contenu">
        <div class="menuData">
            <label for="data">Choisir data :</label>
            <select id="data" name="data">
                <option value="moyenne">M</option>
                <option value="se">SD</option>
                <option value="wrost">W</option>
            </select>
        </div>
			<ul class="data-list">
  <?php foreach ($diagnostics as $diagnostic): ?>
    <li>
	<a href="tumeurVisu.php?Id-tumeur=<?php echo urlencode($diagnostic['Id-tumeur']); ?>">
      <?php echo htmlspecialchars($diagnostic['Id-tumeur']); ?>
      <?php echo htmlspecialchars($diagnostic['libelle_diagnostic']); ?>
	  </a>
    </li>
  <?php endforeach; ?>
</ul>
<?php
     if (isset($_GET['Id-tumeur'])) {
		 $Id_tumeur = intval($_GET['Id-tumeur']);
    $requete = $bdd->prepare('SELECT * FROM tumeur WHERE `Id-tumeur` = ?');
    $requete->execute([$Id_tumeur]);
    $tumeur = $requete->fetch();
	if ($tumeur) {?>
	<?php

// Définit le chemin du dossier temporaire
$tempDir = __DIR__ . '/tmp';
$tempFile = $tempDir . '/test.json';

// Assure que le dossier existe
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0777, true); // Crée le dossier avec les permissions nécessaires
}

// Données à écrire dans le fichier JSON
$tumeurData = [
    "rayon_moyen" => $tumeur['rayon_moyen'],
    "perimetre_moyen" => $tumeur['perimetre_moyen'],
    "uniformite_moyenne" => $tumeur['uniformite_moyenne'],
    "concavite_moyenne" => $tumeur['concavite_moyenne'],
    "symetrie_moyenne" => $tumeur['symetrie_moyenne'],
	"dim_fractal_moyenne" => $tumeur['dim_fractal_moyenne']
];


// Écrit les données dans le fichier JSON
if (file_put_contents($tempFile, json_encode($tumeurData, JSON_PRETTY_PRINT))) {
    

} else {
    echo "Erreur lors de la création du fichier JSON.";
	
	
}

$htmlFile = "tmp/graph.html";

$python = 'C:/Users/mayss/AppData/Local/Programs/Python/Python313/python.exe';
$script = 'C:/MAMP/htdocs/GestionP/testvisu.py';

// Exécuter la commande
$command = "$python $script 2>&1";
$output = shell_exec($command);
?>
 <iframe src="tmp/graph.html" width="65%"; height="700" style="border:none;"></iframe>


        <table>
            <tr>
                <td>Rayon</td>
                <td>Périmètre</td>
                <td>Lissage</td>
                <td>Concavité</td>
                <td>Symétrie</td>
                <td>fractal-dim</td>
				
            </tr>
            <tr>
                <td><?php echo htmlspecialchars($tumeur['rayon_moyen']); ?></td>
                <td><?php echo htmlspecialchars($tumeur['perimetre_moyen']); ?></td>
                <td><?php echo htmlspecialchars($tumeur['uniformite_moyenne']); ?></td>
                <td><?php echo htmlspecialchars($tumeur['concavite_moyenne']); ?></td>
                <td><?php echo htmlspecialchars($tumeur['symetrie_moyenne']); ?></td>
                <td><?php echo htmlspecialchars($tumeur['dim_fractal_moyenne']); ?></td>
				
            </tr>
</table>			<?php 
			 } else {
        echo "Aucune tumeur trouvée avec cet ID.";
    }
} else {
    echo "ID de tumeur non spécifié dans l'URL.";
}?>
        </table>
    </div>

</body>
</html>