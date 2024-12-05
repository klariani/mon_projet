<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
$host = "localhost";
$username = "root";
$password = "root"; // Remplacez par votre mot de passe
$dbname = "cancer"; // Remplacez par le nom réel de la base

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Vérification du nom de la table
$table_name = "tumeur"; // Remplacez par le bon nom de la table

// Vérifiez si la table existe
$table_check = $conn->query("SHOW TABLES LIKE '$table_name'");
if ($table_check->num_rows == 0) {
    die("La table '$table_name' n'existe pas dans la base de données.");
}

// Récupérer les colonnes pour le menu déroulant
$columns = [];
$result = $conn->query("SHOW COLUMNS FROM `$table_name`");
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Exploration</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<link rel="stylesheet" href="style1.css" type="text/css" media="screen" />
	<style>
		.var{color : black}

        #texte {
            text-align: center;
            margin: 20px auto;
            padding: 20px;
            max-width: 800px;
        }
		
        #texte p {
            font-size: 24px;
            font-weight: bold;
            color: #007BFF;
            margin-bottom: 20px;
        }
        #contenu {
			color : black;
            margin: 20px auto;
            max-width: 800px;
            text-align: center;
            position: relative;
        }
        #contenu canvas {
            display: block;
            margin: 0 auto;
        }

    </style>
</head>

<body>
	<div class="navigation">
		<ul>
			<li><a href="exploration.php">Exploration</a></li>
			<li><a href="statistique.html">Statistique</a></li>
			<li><a href="analyseChoix.php">Visualisation</a></li>
			<li><a href="prediction.php">Prédiction</a></li>
			<li><a href="login.php">Compte</a></li>
		</ul>
	</div>
	<img src="img/Capture d'écran 2024-10-26 081223.png">
	<div id="contenu">
		<div id="texte">
			<p>Nuage de points</p>
			<form method="POST">
				<label class="var" for="varX">Variable X :</label>
				<select name="varX">
					<?php foreach ($columns as $column): ?>
						<option value="<?= $column ?>"><?= $column ?></option>
					<?php endforeach; ?>
				</select>

				<label class="var" for="varY">Variable Y :</label>
				<select name="varY">
					<?php foreach ($columns as $column): ?>
						<option value="<?= $column ?>"><?= $column ?></option>
					<?php endforeach; ?>
				</select>

				<button type="submit">Générer le graphique</button>
			</form>
		</div>
	
		<?php
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$varX = $_POST['varX'];
			$varY = $_POST['varY'];

			// Récupérer les données
			$data = [];
			$query = "SELECT `$varX`, `$varY` FROM `$table_name`"; // Requête dynamique
			$result = $conn->query($query);
			if (!$result) {
				die("Erreur dans la requête SQL : " . $conn->error);
			}
			while ($row = $result->fetch_assoc()) {
				$data[] = $row;
			}
		?>

		<canvas id="scatterChart" width="800" height="400"></canvas>
		<script>
			const data = {
				datasets: [{
					label: 'Nuage de points',
					data: <?= json_encode(array_map(fn($row) => ['x' => (float) $row[$varX], 'y' => (float) $row[$varY]], $data)) ?>,
					backgroundColor: 'rgba(75, 192, 192, 0.5)'
				}]
			};

			const config = {
				type: 'scatter',
				data: data,
				options: {
					scales: {
						x: {
							title: {
								display: true,
								text: '<?= $varX ?>'
							}
						},
						y: {
							title: {
								display: true,
								text: '<?= $varY ?>'
							}
						}
					}
				}
			};

			new Chart(
				document.getElementById('scatterChart'),
				config
			);
		</script>
	</div>
    <?php } ?>

</body>
</html>
