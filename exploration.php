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
    <title>Exploration des données</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
	<div class="navigation">
		<ul>
			<li><a href="exploration.html">Exploration</a></li>
			<li><a href="statistique.html">Statistique</a></li>
			<li><a href="analyseChoix.php">Visualisation</a></li>
			<li><a href="prediction.html">Prédiction</a></li>
			<li><a href="login.php">Compte</a></li>
		</ul>
	</div>
    <h1>Exploration des données : Nuage de points</h1>
    <form method="POST">
        <label for="varX">Variable X :</label>
        <select name="varX" id="varX">
            <?php foreach ($columns as $column): ?>
                <option value="<?= $column ?>"><?= $column ?></option>
            <?php endforeach; ?>
        </select>

        <label for="varY">Variable Y :</label>
        <select name="varY" id="varY">
            <?php foreach ($columns as $column): ?>
                <option value="<?= $column ?>"><?= $column ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Générer le graphique</button>
    </form>

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

    <canvas id="scatterChart" width="800" height="600"></canvas>
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
    <?php } ?>

</body>
</html>

