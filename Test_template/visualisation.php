<?php

require "./getBd.php";
$bdd = getBD();
session_start();

 $query = $bdd->prepare("SELECT tumeur.`Id-tumeur`, diagnostic.libelle_diagnostic FROM tumeur, diagnostic WHERE tumeur.code_diagnostic = diagnostic.code_diagnostic");
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
	 <script>
        function submitForm() {
            document.getElementById('autoSubmitForm').submit();
        }
    </script>
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
    </div><a href="home.html">
    <img src="./image/Capture d'écran 2024-10-26 081223.png">
</a>

    <div id="contenu">
        <div class="menuData">
            <label for="data">Choisir data :</label>
            <select id="data" name="data">
                <option value="moyenne" selected >M</option>
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
                <td>21.71</td>
                <td>140,9</td>
                <td>0.0934</td>
                <td>0.1168</td>
                <td>0.1717</td>
				<td>0.0611</td>
            </tr>
        </table>
    </div>
	<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($_POST['data'])) {
            // Récupérer l'option sélectionnée
            $selectedOption = $_POST['data'];

            // Afficher l'option sélectionnée
            echo "<p>Vous avez sélectionné : " . htmlspecialchars($selectedOption) . "</p>";
        } else {
            echo "<p>Aucune option n'a été sélectionnée.</p>";
        }
    }
    ?>
</body>
</html>