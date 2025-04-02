<<<<<<< HEAD
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$host = "localhost";
$username = "root";
$password = ""; 
$dbname = "cancer"; 

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

$table_name = "tumeur";

$table_check = $conn->query("SHOW TABLES LIKE 'tumeur'");
if ($table_check->num_rows == 0) {
    die("La table 'tumeur' n'existe pas dans la base de données.");
}

// Récupérer les colonnes pour le menu déroulant
$columns = [];
$result = $conn->query("SHOW COLUMNS FROM `tumeur`");
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
    <link href="https://fonts.googleapis.com/css2?family=Kaisei+HarunoUmi&display=swap" rel="stylesheet">
	
    <style>
        .var { color: black; }
		img{
    height: auto;
    max-width: 100%;
	width: 44px;
    height: 44px;
	position : fixed ;
	top:0;
	z-index: 30; 
}

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
            color: black;
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
            <li><a href="analyseChoix.php">Statistique</a></li>
            <li><a href="visualisation.php">Visualisation</a></li>
            <li><a href="prediction.html">Prédiction</a></li>
            <li><a href="login.php">Compte</a></li>
        </ul>
    </div>
    <a href="home.html">
    <img src="./image/Capture d'écran 2024-10-26 081223.png">
</a>
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
                <button type="submit">Générer le graphique</button>
            </form>
        </div>
    
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $varX = $_POST['varX'];

            // Récupérer les données avec les catégories B et M
            $dataB = [];
            $dataM = [];
            $query = "SELECT `tumeur`.`$varX` AS variable_x, diagnostic.`libelle_diagnostic` AS type_diagnostic FROM diagnostic,`tumeur` WHERE  `tumeur`.`code_diagnostic` = diagnostic.`code_diagnostic`;";
			$result = $conn->query($query);

			if (!$result) {
				die("Erreur dans la requête SQL : " . $conn->error);
			}

			while ($row = $result->fetch_assoc()) {
				// Vérification des valeurs B et M
				if ($row['type_diagnostic'] === 'B') {
					$dataB[] = ['x' => count($dataB) + 1, 'y' => (float)$row['variable_x']];
				} elseif ($row['type_diagnostic'] === 'M') {
					$dataM[] = ['x' => count($dataM) + 1, 'y' => (float)$row['variable_x']];
				}
			}

        ?>

        <canvas id="scatterChart" width="800" height="400"></canvas>
        <script>
            const data = {
                datasets: [
                    {
                        label: 'Bénin (B)',
                        data: <?= json_encode($dataB) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)' // Bleu
                    },
                    {
                        label: 'Malin (M)',
                        data: <?= json_encode($dataM) ?>,
                        backgroundColor: 'rgba(255, 159, 64, 0.5)' // Orange
                    }
                ]
            };

            const config = {
                type: 'scatter',
                data: data,
                options: {
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Index des points'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: '<?= $varX ?>'
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
=======
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$host = "localhost";
$username = "root";
$password = "root"; 
$dbname = "cancer"; 

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

$table_name = "tumeur";

<<<<<<< HEAD
$table_check = $conn->query("SHOW TABLES LIKE 'tumeur'");
if ($table_check->num_rows == 0) {
    die("La table 'tumeur' n'existe pas dans la base de données.");
=======
$table_check = $conn->query("SHOW TABLES LIKE '$table_name'");
if ($table_check->num_rows == 0) {
    die("La table '$table_name' n'existe pas dans la base de données.");
>>>>>>> 6dacdfd6d34808ed0c9f50e27043adfb9a4d05b2
}

// Récupérer les colonnes pour le menu déroulant
$columns = [];
<<<<<<< HEAD
$result = $conn->query("SHOW COLUMNS FROM `tumeur`");
=======
$result = $conn->query("SHOW COLUMNS FROM `$table_name`");
>>>>>>> 6dacdfd6d34808ed0c9f50e27043adfb9a4d05b2
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
<<<<<<< HEAD
    <link href="https://fonts.googleapis.com/css2?family=Kaisei+HarunoUmi&display=swap" rel="stylesheet">
	
    <style>
        .var { color: black; }
		img{
    height: auto;
    max-width: 100%;
	width: 44px;
    height: 44px;
	position : fixed ;
	top:0;
	z-index: 30; 
}
=======
    <style>
        .var { color: black; }
>>>>>>> 6dacdfd6d34808ed0c9f50e27043adfb9a4d05b2

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
            color: black;
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
<<<<<<< HEAD
            <li><a href="analyseChoix.php">Statistique</a></li>
            <li><a href="visualisation.php">Visualisation</a></li>
            <li><a href="prediction.html">Prédiction</a></li>
            <li><a href="login.php">Compte</a></li>
        </ul>
    </div>
    <a href="home.html">
    <img src="./image/Capture d'écran 2024-10-26 081223.png">
</a>
=======
            <li><a href="statistique.html">Statistique</a></li>
            <li><a href="analyseChoix.php">Visualisation</a></li>
            <li><a href="prediction.php">Prédiction</a></li>
            <li><a href="login.php">Compte</a></li>
        </ul>
    </div>
    <img src="img/Capture d'écran 2024-10-26 081223.png">
>>>>>>> 6dacdfd6d34808ed0c9f50e27043adfb9a4d05b2
    <div id="contenu">
        <div id="texte">
            <p>Nuage de points</p>
            <form method="POST">
                <label class="var" for="varX">Variable X :</label>
                <select name="varX">
                    <?php foreach ($columns as $column): ?>
<<<<<<< HEAD
					
=======
>>>>>>> 6dacdfd6d34808ed0c9f50e27043adfb9a4d05b2
                        <option value="<?= $column ?>"><?= $column ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Générer le graphique</button>
            </form>
        </div>
    
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $varX = $_POST['varX'];

            // Récupérer les données avec les catégories B et M
            $dataB = [];
            $dataM = [];
<<<<<<< HEAD
            $query = "SELECT `tumeur`.`$varX` AS variable_x, diagnostic.`libelle_diagnostic` AS type_diagnostic FROM diagnostic,`tumeur` WHERE  `tumeur`.`code_diagnostic` = diagnostic.`code_diagnostic`;";
=======
            $query = "SELECT `$table_name`.`$varX` AS variable_x, diagnostique.libelle_diagnostic AS type_diagnostic FROM `$table_name` JOIN diagnostic ON `$table_name`.`Id-tumeur` = diagnostic.`Id-tumeur` JOIN diagnostique ON diagnostique.code_diagnostic = diagnostic.code_diagnostic;";
>>>>>>> 6dacdfd6d34808ed0c9f50e27043adfb9a4d05b2
			$result = $conn->query($query);

			if (!$result) {
				die("Erreur dans la requête SQL : " . $conn->error);
			}

			while ($row = $result->fetch_assoc()) {
				// Vérification des valeurs B et M
				if ($row['type_diagnostic'] === 'B') {
					$dataB[] = ['x' => count($dataB) + 1, 'y' => (float)$row['variable_x']];
				} elseif ($row['type_diagnostic'] === 'M') {
					$dataM[] = ['x' => count($dataM) + 1, 'y' => (float)$row['variable_x']];
				}
			}

        ?>

        <canvas id="scatterChart" width="800" height="400"></canvas>
        <script>
            const data = {
                datasets: [
                    {
                        label: 'Bénin (B)',
                        data: <?= json_encode($dataB) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)' // Bleu
                    },
                    {
                        label: 'Malin (M)',
                        data: <?= json_encode($dataM) ?>,
                        backgroundColor: 'rgba(255, 159, 64, 0.5)' // Orange
                    }
                ]
            };

            const config = {
                type: 'scatter',
                data: data,
                options: {
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Index des points'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: '<?= $varX ?>'
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
>>>>>>> a1dc4dba1a2451c38c6a10c5a7b0438a8a1b82ab
