<?php
// ========================
// Paramètres de connexion
// ========================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$username = "root";
$password = "";
$dbname = "cancer";

// Connexion MySQL
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Vérifier la table tumeur
$table_check = $conn->query("SHOW TABLES LIKE 'tumeur'");
if ($table_check->num_rows == 0) {
    die("La table 'tumeur' n'existe pas dans la BDD 'cancer'.");
}

// Récupérer la liste des colonnes pour la section Exploration
$columns = [];
$resultCols = $conn->query("SHOW COLUMNS FROM tumeur");
while ($row = $resultCols->fetch_assoc()) {
    $columns[] = $row['Field'];
}

// Caractéristiques (pour l'analyse statistique)
$caracteristiques = [];
foreach ($columns as $field) {
    if ($field !== 'Id-tumeur') {
        $caracteristiques[] = $field;
    }
}

// Lecture diagnostics (pour la section Visualisation)
$query = $conn->query("SELECT tumeur.`Id-tumeur`, diagnostic.libelle_diagnostic 
                       FROM tumeur 
                       JOIN diagnostic ON tumeur.code_diagnostic = diagnostic.code_diagnostic");

$diagnostics = [];
if ($query) {
    while ($row = $query->fetch_assoc()) {
        $diagnostics[] = $row;
    }
} else {
    die("Erreur SQL : " . $conn->error);
}

$baseDir = "C:/wamp64/www/GestionP";
// =============================
// Lecture de test_results.json
// =============================
$testResultsPath = $baseDir . "/test_template/test_results.json";
$testResults = [];
if (file_exists($testResultsPath)) {
    $jsonContent = file_get_contents($testResultsPath);
    $testResults = json_decode($jsonContent, true);
}

// Extraction des données du JSON
$confMatrix = $testResults['confusion_matrix'] ?? [];
$classReport = $testResults['classification_report'] ?? [];
// Pour la distribution, on s'appuie sur le support de chaque classe
$distData = [];
foreach (['benign','malignant','normal'] as $cls) {
    if (isset($classReport[$cls]['support'])) {
        $distData[$cls] = $classReport[$cls]['support'];
    }
}
// Pour le clustering 3D, ROC et historique, on utilise des valeurs par défaut si elles ne sont pas présentes.
$clusterData = $testResults['clustering_data'] ?? [];
$rocData = $testResults['roc_data'] ?? [];
$trainHist = $testResults['training_history'] ?? [];

// =============================
// Lecture de predictions_history.csv
// =============================
$historyFile = $baseDir . "/predictions_history.csv";
$historyData = [];
if (file_exists($historyFile)) {
    if (($handle = fopen($historyFile, 'r')) !== false) {
        fgetcsv($handle); // en-tête
        while (($row = fgetcsv($handle, 1000, ",")) !== false) {
            $historyData[] = [
                'date' => $row[0],
                'image' => $row[1],
                'classe' => $row[2],
                'confiance' => floatval($row[3])
            ];
        }
        fclose($handle);
    }
}
?>

<!DOCTYPE HTML>
<html lang="fr">
<head>
    <title>Oncoanalyse - Homepage</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />

    <!-- Feuille de style -->
    <link rel="stylesheet" href="assets/css/main.css" />
    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>

    <!-- Chart.js (pour la section Exploration) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Plotly.js pour les graphes de la section Prediction -->
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>

    <!-- FontAwesome (optionnel) -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css"
          integrity="sha512-..."
          crossorigin="anonymous"
          referrerpolicy="no-referrer" />
 

</head>
<body class="is-preload">

<div id="wrapper">

    <!-- Header -->
    <header id="header" class="alt">
        <h1>Oncoanalyse</h1>
        <p>Prédire la nature des tumeurs du sein</p>
    </header>

    <!-- Nav -->
    <nav id="nav">
        <ul>
            <li><a href="#apropos" class="active">A propos nous</a></li>
            <li><a href="#exploration">Exploration</a></li>
            <li><a href="#visualisation">Visualisation</a></li>
            <li><a href="#prediction">Prédiction</a></li>
            <li><a href="#compte">Compte</a></li>
        </ul>
    </nav>

    <!-- Main -->
    <div id="main">
      <section class="wave-two-cols">
					<!-- Colonne gauche : fond dégradé + vague -->
					<div class="left-col">
					  <div class="text-content">
						<h2>Présentation</h2>
						<p>
						  Ce projet utilise les données des tumeurs du sein pour prédire leur nature 
						  (bénigne ou maligne). En exploitant des caractéristiques comme le rayon 
						  et la texture, il vise à améliorer le diagnostic et réduire les biopsies 
						  invasives grâce à des modèles prédictifs et des visualisations interactives.
						</p>
						<a href="generic.html" class="btn-learn-more">Learn More</a>
					  </div>
					</div>
				  
					<!-- Colonne droite : l'image -->
					<div class="right-col">
					  <img src="../image/img01.png" alt="Oncoanalyse Background" />
					</div>
			 </section>
				  
            <!-- Second Section (avec le graphe) -->
            <section id="exploration" class="main special">
                <!-- Formulaire pour choisir la variable X + Génération du scatter chart -->
                <div style="margin-top: 2rem; text-align: center;">
                    <h3>Nuage de points (Bénin vs Malin)</h3>
                    <form method="POST">
                        <label for="varX" style="color: black;">Variable Y :</label>
                        <select name="varX">
                            <?php foreach ($columns as $col): ?>
                                <option value="<?= $col ?>"><?= $col ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Générer le graphique</button>
                    </form>
					<p>Ci-dessus, vous pouvez générer un nuage de points en choissant une variable Y pour visualiser les différents entre une tumeur bénigne et maligne.</p>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
						$varX = $_POST['varX'];

						// Récupérer les données avec les catégories B et M
						$dataB = [];
						$dataM = [];
						$query = "SELECT `tumeur`.`$varX` AS variable_x, diagnostic.`libelle_diagnostic` AS type_diagnostic FROM `diagnostic`, `tumeur` WHERE `tumeur`.`code_diagnostic` = diagnostic.`code_diagnostic`";
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
                        <!-- Canvas pour Chart.js -->
                        <canvas id="scatterChart" width="800" height="400"></canvas>
                        <!-- Chart.js -->
                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                        <script>
                            const data = {
                                datasets: [
                                    {
                                        label: 'Bénin (B)',
                                        data: <?= json_encode($dataB) ?>,
                                        backgroundColor: 'rgba(54, 162, 235, 0.5)'
                                    },
                                    {
                                        label: 'Malin (M)',
                                        data: <?= json_encode($dataM) ?>,
                                        backgroundColor: 'rgba(255, 159, 64, 0.5)'
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
                                                text: 'Tumeur'
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
                        <?php
                    } // fin if POST
                    ?>
                </div>
                <!-- Fin du scatter chart -->

                <footer class="major">
                    <ul class="actions special">
                        <li><a href="" class="button">EN savoir plus</a></li>
                    </ul>
                </footer>
            </section>

        <!-- ANALYSE (inchangé) -->
        <section class="wave-analyse-fullwidth" id="analyse">
            <div class="analyse-container">
                <div class="text-content">
                    <h2>Analyse Statistique</h2>
                    <p>Sélectionnez <strong>au moins 1 caractéristique</strong> à analyser :</p>
                    <form method="post" action="analyseRésultat.php">
                        <div class="checkbox-container">
                            <div class="checkbox-scroll">
                                <?php foreach ($caracteristiques as $carac): ?>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="caracteristiques[]" value="<?= htmlspecialchars($carac); ?>" class="checkbox-item">
                                        <?= htmlspecialchars($carac); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="button-container" style="margin-top:1.5rem;">
                            <button type="submit" id="submit-button" disabled class="btn-learn-more">Analyser</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- VISUALISATION (inchangé) -->
        <section class="wave-two-cols" id="visualisation">
            <div class="left-col">
                <div class="text-content">
                    <h2>Visualisation des données</h2>
                    <p>Explorez les identifiants des tumeurs associés à leurs diagnostics, et sélectionnez-les pour obtenir une visualisation individuelle.</p>
                    <div class="menuData" style="margin:1rem 0;">
                        <label for="data" style="color:#fff; font-weight:bold;">Choisir data :</label><br>
                        <select id="data" name="data" style="margin-top:0.5rem; padding:0.5rem; border-radius:6px;">
                            <option value="moyenne">M</option>
                            <option value="se">SD</option>
                            <option value="worst">W</option>
                        </select>
                    </div>
                    <div class="checkbox-scroll" style="max-height:220px; overflow-y:auto; background:rgba(255,255,255,0.1); padding:1rem; border-radius:8px;">
                        <ul style="list-style:none; padding:0;">
                            <?php foreach ($diagnostics as $d): ?>
                                <li style="margin-bottom:0.5rem;">
                                    <a href="tumeurVisu.php?Id-tumeur=<?= urlencode($d['Id-tumeur']); ?>" style="color:#fff; text-decoration:underline;">
                                        <?= htmlspecialchars($d['Id-tumeur']) ?> - <?= htmlspecialchars($d['libelle_diagnostic']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="right-col">
                <div style="text-align:center;">
                    <h3 style="color:#444;">Exemple de données</h3>
                    <table style="width:100%; margin-top:1rem; border-collapse:collapse;">
                        <tr style="background:#f0eefb;">
                            <td>Rayon</td>
                            <td>Périmètre</td>
                            <td>Lissage</td>
                            <td>Concavité</td>
                            <td>Symétrie</td>
                            <td>Fractal-dim</td>
                        </tr>
                        <tr style="background:#fff;">
                            <td>21.71</td>
                            <td>140.9</td>
                            <td>0.0934</td>
                            <td>0.1168</td>
                            <td>0.1717</td>
                            <td>0.0611</td>
                        </tr>
                    </table>
                </div>
            </div>
        </section>
<!-- SECTION "PREDICTION" (MODIFIÉE avec Graphes organisés) -->
<section id="prediction">
  <div class="left-col">
    <div class="text-content">
      <h2>Prédiction par Image Médicale</h2>
      <p>
        Téléchargez une image médicale (IRM, scanner, etc.) pour bénéficier d'une analyse instantanée et précise effectuée par des modèles avancés d'intelligence artificielle.
        Obtenez rapidement des résultats diagnostiques fiables afin de faciliter la prise de décision clinique et optimiser la prise en charge des patients.
      </p>
    </div>
  </div>

  <div class="right-col">
    <div class="analysis-section">
      <!-- Formulaire d'importation -->
      <form id="upload-form" onsubmit="return false;">
        <label for="file" class="upload-area" id="upload-label">
          <i class="fas fa-cloud-upload-alt"></i>
          <h4>Importez votre image</h4>
          <p>JPG, PNG</p>
          <div id="preview-container">
            <img id="preview-image" alt="Aperçu" style="max-width:200px; display:none;" />
          </div>
        </label>
        <input type="file" id="file" name="file" accept="image/*" />

        <div class="progress" id="upload-progress" style="display:none;">
          <div class="progress-bar progress-bar-striped" role="progressbar" style="width:0%;"></div>
        </div>

        <button type="button" class="btn btn-light mt-3" id="analyze-btn" onclick="analyzeImage()">
          <i class="fas fa-search me-2"></i>Analyser
        </button>
      </form>

      <div class="card mt-4" id="result-card" style="display:none;">
        <div class="card-header">
          <h5 class="mb-0">Résultats de l'analyse</h5>
        </div>
        <div class="card-body">
          <img id="result-image" class="img-fluid mb-4" />
          <div id="result-content"></div>
        </div>
      </div>

      <!-- Titre de section des graphiques -->
      <h3 class="charts-title">Graphes d'Analyse</h3>

      <div class="charts-container">
        <div class="chart-box">
          <div class="chart-title">Distribution des Classes</div>
          <div id="distributionChart" class="chart-area"></div>
        </div>

        <div class="chart-box">
          <div class="chart-title">Matrice de Confusion</div>
          <div id="confusionMatrixChart" class="chart-area"></div>
        </div>

        <div class="chart-box">
          <div class="chart-title">Clustering 3D</div>
          <div id="clustering3D" class="chart-area"></div>
        </div>

        <div class="chart-box">
          <div class="chart-title">Courbe ROC</div>
          <div id="rocCurveChart" class="chart-area"></div>
        </div>

        <div class="chart-box">
          <div class="chart-title">Historique d'Entraînement</div>
          <div id="trainingHistoryChart" class="chart-area"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Script Plotly et code JavaScript sera inséré ici -->
<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
<!-- Insérez ensuite le script JavaScript amélioré fourni -->
<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await fetch('http://localhost:5000/model_info');
        const data = await res.json();
        
        // Configuration commune améliorée pour tous les graphiques
        const config = {
            responsive: true,
            displayModeBar: false,
            toImageButtonOptions: {
                format: 'png',
                filename: 'graphique_oncoanalyse',
                height: 500,
                width: 700,
                scale: 2
            }
        };
        
        // GRAPHIQUE 1: DISTRIBUTION DES CLASSES - Version épurée et optimisée
        Plotly.newPlot('distributionChart', [{
            x: ['Bénin', 'Malin', 'Normal'],
            y: Object.values(data.classification_report).map(c => c.support).slice(0, 3),
            type: 'bar',
            marker: {
                color: ['#5D9CEC', '#ED5565', '#48CFAD'],
                opacity: 0.85
            },
            text: Object.values(data.classification_report).map(c => c.support).slice(0, 3),
            textposition: 'auto',
            hoverinfo: 'y'
        }], { 
            ...config,
            margin: { t: 10, r: 20, l: 50, b: 70 }, // Marges optimisées
            autosize: true, // Important pour la responsivité
            yaxis: { 
                title: {
                    text: "Nombre d'échantillons",
                    font: { size: 11, color: '#777' }
                },
                gridcolor: '#f5f5f5'
            },
            xaxis: {
                title: {
                    text: "Type de tumeur",
                    font: { size: 11, color: '#777' }
                }
            },
            paper_bgcolor: 'transparent',
            plot_bgcolor: 'transparent'
        });

        // GRAPHIQUE 2: MATRICE DE CONFUSION - Optimisé
        const classes = ['Bénin', 'Malin', 'Normal'];
        Plotly.newPlot('confusionMatrixChart', [{
            z: data.confusion_matrix,
            x: classes,
            y: classes,
            type: 'heatmap',
            colorscale: [
                [0, '#F9FAFB'],
                [0.25, '#E4ECF7'],
                [0.5, '#C9D9F0'],
                [0.75, '#9ABBEA'],
                [1, '#5D9CEC']
            ],
            showscale: false,
            hovertemplate: 'Réel: %{y}<br>Prédit: %{x}<br>Valeur: %{z}<extra></extra>'
        }], { 
            ...config,
            margin: { t: 10, r: 20, l: 80, b: 60 }, // Optimisé
            autosize: true,
            paper_bgcolor: 'transparent',
            plot_bgcolor: 'transparent'
        });

        // GRAPHIQUE 3: CLUSTERING 3D - Optimisé avec meilleure vue
        const clusters = {};
        const colors = {
            'benin': '#5D9CEC',  // Bleu
            'malin': '#ED5565',  // Rouge
            'normal': '#48CFAD'  // Vert
        };
        
        data.clustering_data.forEach(p => {
            if (!clusters[p.classe]) {
                clusters[p.classe] = { 
                    x: [], y: [], z: [], 
                    mode: 'markers', 
                    type: 'scatter3d', 
                    name: p.classe === 'benin' ? 'Bénin' : 
                          p.classe === 'malin' ? 'Malin' : 'Normal',
                    marker: {
                        size: 4.5,
                        color: colors[p.classe],
                        opacity: 0.8
                    },
                    hovertemplate: '%{meta}<extra></extra>',
                    meta: []
                };
            }
            clusters[p.classe].x.push(p.x);
            clusters[p.classe].y.push(p.y);
            clusters[p.classe].z.push(p.z);
            clusters[p.classe].meta.push(`${p.classe}: (${p.x.toFixed(1)}, ${p.y.toFixed(1)}, ${p.z.toFixed(1)})`);
        });
        
        Plotly.newPlot('clustering3D', Object.values(clusters), { 
            ...config,
            margin: { t: 0, r: 0, l: 0, b: 0 }, // Marges minimales pour maximiser l'espace 3D
            autosize: true,
            scene: {
                xaxis: { title: 'Composante 1', backgroundcolor: 'transparent', gridcolor: '#eeeeee' },
                yaxis: { title: 'Composante 2', backgroundcolor: 'transparent', gridcolor: '#eeeeee' },
                zaxis: { title: 'Composante 3', backgroundcolor: 'transparent', gridcolor: '#eeeeee' },
                aspectratio: { x: 1, y: 1, z: 0.8 },
                camera: {
                    eye: { x: 1.5, y: 1.5, z: 1 }
                }
            },
            paper_bgcolor: 'transparent',
            legend: {
                x: 0,
                y: 1,
                orientation: 'h',
                font: { size: 12 },
                bgcolor: 'rgba(255, 255, 255, 0.5)'
            }
        });

        // GRAPHIQUE 4: COURBE ROC - Optimisé
        const rocTraces = [
            // Ligne de référence (classifier aléatoire)
            {
                x: [0, 1],
                y: [0, 1],
                mode: 'lines',
                name: 'Aléatoire',
                line: {
                    dash: 'dot',
                    width: 1,
                    color: '#CCCCCC'
                },
                hoverinfo: 'skip'
            }
        ];
        
        // Ajouter les courbes ROC pour chaque classe
        data.roc_data.forEach(c => {
            if (c.class !== 'micro-average' && c.class !== 'macro-average') {
                const className = c.class === 'benign' ? 'Bénin' : 
                                c.class === 'malignant' ? 'Malin' : 'Normal';
                const color = c.class === 'benign' ? '#5D9CEC' : 
                             c.class === 'malignant' ? '#ED5565' : '#48CFAD';
                             
                rocTraces.push({ 
                    x: c.fpr, 
                    y: c.tpr, 
                    mode: 'lines', 
                    name: `${className} (AUC=${c.auc.toFixed(3)})`,
                    line: {
                        width: 2.5,
                        color: color
                    },
                    hovertemplate: 'Sensibilité: %{y:.3f}<br>1-Spécificité: %{x:.3f}<extra></extra>'
                });
            }
        });
        
        Plotly.newPlot('rocCurveChart', rocTraces, { 
            ...config,
            margin: { t: 10, r: 20, l: 60, b: 60 }, // Optimisé
            autosize: true,
            xaxis: { 
                title: {
                    text: "Taux de faux positifs (1-Spécificité)",
                    font: { size: 11, color: '#777' }
                },
                range: [0, 1],
                gridcolor: '#f5f5f5'
            },
            yaxis: { 
                title: {
                    text: "Taux de vrais positifs (Sensibilité)",
                    font: { size: 11, color: '#777' }
                },
                range: [0, 1],
                gridcolor: '#f5f5f5'
            },
            paper_bgcolor: 'transparent',
            plot_bgcolor: 'transparent',
            legend: {
                x: 0.01,
                y: 0.99,
                bgcolor: 'rgba(255, 255, 255, 0.5)',
                bordercolor: '#E2E2E2',
                borderwidth: 1,
                font: { size: 10 }
            },
            shapes: [{
                type: 'rect',
                x0: 0,
                y0: 0,
                x1: 1,
                y1: 1,
                line: { width: 0 },
                fillcolor: 'rgba(240, 240, 240, 0.2)'
            }]
        });

        // GRAPHIQUE 5: HISTORIQUE D'ENTRAÎNEMENT - Optimisé
        const epochs = Array.from({length: data.training_history.train_accuracy.length}, (_, i) => i + 1);
        
        Plotly.newPlot('trainingHistoryChart', [
            { 
                x: epochs, 
                y: data.training_history.train_accuracy, 
                name: 'Précision (entrainement)',
                mode: 'lines',
                line: { 
                    color: '#5D9CEC', 
                    width: 2.5 
                },
                hovertemplate: 'Époque %{x}<br>Précision: %{y:.3f}<extra></extra>'
            },
            { 
                x: epochs, 
                y: data.training_history.val_accuracy, 
                name: 'Précision (validation)',
                mode: 'lines',
                line: { 
                    color: '#5D9CEC', 
                    width: 2.5, 
                    dash: 'dot' 
                },
                hovertemplate: 'Époque %{x}<br>Précision: %{y:.3f}<extra></extra>'
            },
            { 
                x: epochs, 
                y: data.training_history.train_loss, 
                name: 'Perte (entrainement)',
                mode: 'lines',
                line: { 
                    color: '#ED5565', 
                    width: 2.5 
                },
                yaxis: 'y2',
                hovertemplate: 'Époque %{x}<br>Perte: %{y:.3f}<extra></extra>'
            },
            { 
                x: epochs, 
                y: data.training_history.val_loss, 
                name: 'Perte (validation)',
                mode: 'lines',
                line: { 
                    color: '#ED5565', 
                    width: 2.5, 
                    dash: 'dot' 
                },
                yaxis: 'y2',
                hovertemplate: 'Époque %{x}<br>Perte: %{y:.3f}<extra></extra>'
            }
        ], { 
            ...config,
            margin: { t: 10, r: 60, l: 60, b: 60 }, // Optimisé
            autosize: true,
            xaxis: { 
                title: {
                    text: "Époques",
                    font: { size: 11, color: '#777' }
                },
                gridcolor: '#f5f5f5',
                tickmode: 'linear',
                tick0: 1,
                dtick: Math.max(1, Math.ceil(epochs.length / 10))
            },
            yaxis: { 
                title: {
                    text: "Précision",
                    font: { size: 11, color: '#5D9CEC' }
                },
                range: [0, 1],
                gridcolor: '#f5f5f5',
                tickformat: '.1f'
            }, 
            yaxis2: { 
                title: {
                    text: "Perte",
                    font: { size: 11, color: '#ED5565' }
                },
                overlaying: 'y', 
                side: 'right',
                gridcolor: '#f5f5f5',
                tickformat: '.1f'
            },
            paper_bgcolor: 'transparent',
            plot_bgcolor: 'transparent',
            legend: {
                y: 1,
                x: 0.01,
                orientation: 'h',
                bgcolor: 'rgba(255, 255, 255, 0.5)',
                bordercolor: '#E2E2E2',
                borderwidth: 1,
                font: { size: 10 }
            }
        });
        
        // Ajout d'un redimensionnement lorsque la fenêtre change de taille
        window.addEventListener('resize', () => {
            const graphIds = ['distributionChart', 'confusionMatrixChart', 'clustering3D', 'rocCurveChart', 'trainingHistoryChart'];
            graphIds.forEach(id => {
                const graphDiv = document.getElementById(id);
                if (graphDiv) Plotly.Plots.resize(graphDiv);
            });
        });
        
    } catch (error) {
        console.error("Erreur lors du chargement des graphiques:", error);
        // Message d'erreur discret et élégant
        document.querySelectorAll('.chart-box > div:not(.chart-title)').forEach(el => {
            el.innerHTML = `
                <div style="height:100%; display:flex; align-items:center; justify-content:center; flex-direction:column; color:#777; text-align:center; padding:10px;">
                    <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#CCC" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <p style="margin-top:15px; font-size:14px;">Impossible de charger les données</p>
                </div>
            `;
        });
    }
});

// Fonction d'analyse d'image améliorée et simplifiée
async function analyzeImage() {
    try {
        const fileInput = document.getElementById("file");
        if (!fileInput.files.length) {
            return; // Ne rien faire si aucun fichier n'est sélectionné
        }
        
        // Interface utilisateur pendant le chargement
        const progressBar = document.querySelector("#upload-progress .progress-bar");
        document.getElementById("upload-progress").style.display = "block";
        document.getElementById("analyze-btn").disabled = true;
        
        // Simuler la progression pour une meilleure expérience utilisateur
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += 5;
            if (progress > 85) progress = 85;
            progressBar.style.width = progress + "%";
        }, 150);
        
        // Envoi de la requête
        const formData = new FormData();
        formData.append("file", fileInput.files[0]);
        
        const res = await fetch("http://localhost:5000/predict", { 
            method: "POST", 
            body: formData 
        });
        
        // Finalisation de la barre de progression
        clearInterval(progressInterval);
        progressBar.style.width = "100%";
        
        const result = await res.json();
        
        // Mettre à jour l'interface avec le résultat
        setTimeout(() => {
            document.getElementById("upload-progress").style.display = "none";
            document.getElementById("analyze-btn").disabled = false;
            
            document.getElementById("result-image").src = result.image_path;
            
            // Formater le résultat
            const confidence = result.confiance.toFixed(1);
            const statusClass = result.classe === 'malignant' ? 'danger' : 'success';
            const statusText = result.classe === 'malignant' ? 'Malin' : 'Bénin';
            
            document.getElementById("result-content").innerHTML = `
                <div style="text-align:center;">
                    <h4 style="color:${statusClass === 'danger' ? '#ED5565' : '#48CFAD'}; font-size:18px; margin-bottom:10px;">
                        Tumeur ${statusText}
                    </h4>
                    <div style="background:#f8f9fa; border-radius:4px; padding:10px; margin-bottom:15px;">
                        <div style="height:8px; background:#eee; border-radius:4px; margin-bottom:6px;">
                            <div style="height:100%; width:${confidence}%; background:${statusClass === 'danger' ? '#ED5565' : '#48CFAD'}; border-radius:4px;"></div>
                        </div>
                        <p style="margin:0; font-size:13px; color:#777;">Confiance: ${confidence}%</p>
                    </div>
                </div>
            `;
            
            document.getElementById("result-card").style.display = "block";
        }, 500);
        
    } catch (error) {
        console.error("Erreur lors de l'analyse:", error);
        document.getElementById("upload-progress").style.display = "none";
        document.getElementById("analyze-btn").disabled = false;
    }
}
</script>

<style>
/* Styles améliorés pour la section Prédiction */
#prediction {
  display: flex;
  flex-wrap: wrap;
  gap: 30px;
  margin-bottom: 60px;
}

.left-col {
  flex: 1;
  min-width: 300px;
}

.right-col {
  flex: 2;
  min-width: 500px;
}

.charts-title {
  margin-top: 40px;
  margin-bottom: 20px;
  font-size: 22px;
  color: #333;
  border-bottom: none;
  padding-bottom: 5px;
}

.charts-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
  gap: 30px;
  margin-top: 20px;
}

.chart-box {
  background: none;
  border: none;
  box-shadow: none;
  margin-bottom: 30px;
}

.chart-title {
  font-size: 16px;
  color: #444;
  margin-bottom: 15px;
  font-weight: 600;
  border: none;
  background: none;
  box-shadow: none;
  padding: 0;
}

.chart-area {
  height: 300px;
  border-radius: 8px;
  background: none;
  border: none;
  box-shadow: none;
}

#clustering3D {
  height: 400px;
}

.upload-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px;
  border: 2px dashed #ddd;
  border-radius: 8px;
  cursor: pointer;
  text-align: center;
  transition: all 0.3s ease;
}

.upload-area:hover {
  border-color: #5D9CEC;
  background-color: rgba(93, 156, 236, 0.05);
}

.upload-area i {
  font-size: 32px;
  color: #5D9CEC;
  margin-bottom: 10px;
}

#file {
  display: none;
}

#result-card {
  border: none;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

#result-card .card-header {
  background-color: #f8f9fa;
  border-bottom: none;
  border-radius: 8px 8px 0 0;
}

#result-image {
  max-height: 200px;
  object-fit: contain;
  margin: 0 auto;
  display: block;
}

/* Supprimer tous les arrière-plans et cadres des graphiques */
.js-plotly-plot .plotly .main-svg {
  background: transparent !important;
}

.js-plotly-plot .plotly .svg-container {
  background: transparent !important;
}

/* Adaptation responsive */
@media (max-width: 992px) {
  .charts-container {
    grid-template-columns: 1fr;
  }
  
  .chart-area {
    height: 250px;
  }
  
  #clustering3D {
    height: 350px;
  }
}
</style>


<!-- (Optionnel) Scripts annexes -->
<script>
document.addEventListener('scroll', function() {
    const secondSection = document.getElementById('second');
    if (!secondSection) return;
    const rect = secondSection.getBoundingClientRect();
    const windowHeight = window.innerHeight || document.documentElement.clientHeight;
    if (rect.top < windowHeight * 0.7) {
        secondSection.classList.add('fade-in'); 
    }
});

// Gestion du bouton "Analyser" désactivé pour le formulaire (si besoin)
const checkboxes = document.querySelectorAll('.checkbox-item');
const submitButton = document.getElementById('submit-button');
function updateSubmitButtonState() {
  const checkedCount = document.querySelectorAll('.checkbox-item:checked').length;
  submitButton.disabled = !(checkedCount >= 1);
}
checkboxes.forEach(checkbox => {
  checkbox.addEventListener('change', updateSubmitButtonState);
});

// Apparition de la section "visualisation" au scroll
document.addEventListener('scroll', function () {
  const visualSection = document.getElementById('visualisation');
  if (!visualSection) return;
  const rect = visualSection.getBoundingClientRect();
  const windowHeight = window.innerHeight || document.documentElement.clientHeight;
  if (rect.top < windowHeight * 0.75) {
    visualSection.classList.add('visible');
  }
});

// Script pour afficher l'image sélectionnée avant l'analyse
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const img = document.getElementById('preview-image');
            img.src = event.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>