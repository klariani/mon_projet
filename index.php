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

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Vérifier la table tumeur
$table_check = $conn->query("SHOW TABLES LIKE 'tumeur'");
if ($table_check->num_rows == 0) {
    die("La table 'tumeur' n'existe pas dans la BDD 'cancer'.");
}

// Récupérer la liste des colonnes pour le <select>
$columns = [];
$resultCols = $conn->query("SHOW COLUMNS FROM tumeur");
while ($row = $resultCols->fetch_assoc()) {
    $columns[] = $row['Field'];
}

// --- SECTION 3 : Correction ici ---
// On utilise la connexion mysqli ($conn), pas PDO ($bdd)
$caracteristiques = [];
foreach ($columns as $field) {
    if ($field !== 'Id-tumeur') {
        $caracteristiques[] = $field;
    }
}
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

?>

<!DOCTYPE HTML>
<html lang="fr">
<head>
    <title>Oncoanalyse - Homepage</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />

    <!-- Feuille de style du template Stellar (modifiée avec nos ajouts) -->
    <link rel="stylesheet" href="assets/css/main.css" />
    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
</head>
<body class="is-preload">

    <!-- Wrapper -->
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
        <section class="wave-two-cols" id="apropos">
  <div class="left-col">
    <div class="text-content">
      <h2>Présentation</h2>
      <p>""
  Oncoanalyse est un outil intelligent conçu pour accompagner les professionnels de santé dans la détection des tumeurs mammaires. Il repose sur l’analyse de données cliniques pour prédire si une tumeur est bénigne ou maligne.
</p>


      <a href="generic.html" class="btn-learn-more">En savoir plus</a>
    </div>
  </div>
  <div class="right-col">
    <img src="image/img01.png" alt="Illustration Oncoanalyse" />
  </div>
</section>


            <!-- Second Section (avec le graphe) -->
            <section id="exploration" class="main special">
                <!-- Formulaire pour choisir la variable X + Génération du scatter chart -->
                <div style="margin-top: 2rem; text-align: center;">
                    <h3>Nuage de points (Bénin vs Malin)</h3>
                    <form method="POST">
                        <label for="varX" style="color: black;">Variable X :</label>
                        <select name="varX">
                            <?php foreach ($columns as $col): ?>
                                <option value="<?= $col ?>"><?= $col ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Générer le graphique</button>
                    </form>

                    <?php
                    // Si le formulaire a été soumis
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $varX = $_POST['varX'];

                        // Requête B / M + la valeur
                        $sql = "SELECT tumeur.$varX AS variable_x, diagnostic.libelle_diagnostic AS type_diagnostic
                                FROM tumeur
                                JOIN diagnostic ON tumeur.code_diagnostic = diagnostic.code_diagnostic";
                        
                        $res = $conn->query($sql);
                        if (!$res) {
                            die("Erreur SQL : " . $conn->error);
                        }

                        $dataB = [];
                        $dataM = [];
                        while ($row = $res->fetch_assoc()) {
                            if ($row['type_diagnostic'] === 'B') {
                                $dataB[] = ['x' => count($dataB)+1, 'y' => (float)$row['variable_x']];
                            } elseif ($row['type_diagnostic'] === 'M') {
                                $dataM[] = ['x' => count($dataM)+1, 'y' => (float)$row['variable_x']];
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
<!-- Section 3 : Analyse sans image -->
<section class="wave-analyse-fullwidth" id="analyse">
  <div class="analyse-container">
    <div class="text-content">
      <h2>Analyse Statistique</h2>
      <p>Sélectionnez <strong>au moins 1 caractéristique</strong> à analyser :</p>

      <!-- Formulaire -->
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
        <div class="button-container" style="margin-top: 1.5rem;">
          <button type="submit" id="submit-button" disabled class="btn-learn-more">Analyser</button>
        </div>
      </form>
    </div>
  </div>
</section>
<!-- Section 4 : Visualisation -->
<section class="wave-two-cols" id="visualisation">
  <div class="left-col">
    <div class="text-content">
      <h2>Visualisation des données</h2>
      <p>Explorez les identifiants des tumeurs associés à leurs diagnostics, et sélectionnez-les pour obtenir une visualisation individuelle.</p>

      <!-- Menu déroulant stylé -->
      <div class="menuData" style="margin: 1rem 0;">
        <label for="data" style="color: #fff; font-weight: bold;">Choisir data :</label><br>
        <select id="data" name="data" style="margin-top: 0.5rem; padding: 0.5rem; border-radius: 6px;">
          <option value="moyenne">M</option>
          <option value="se">SD</option>
          <option value="worst">W</option>
        </select>
      </div>

      <!-- Liste scrollable -->
      <div class="checkbox-scroll" style="max-height: 220px; overflow-y: auto; background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 8px;">
        <ul style="list-style: none; padding: 0;">
          <?php foreach ($diagnostics as $diagnostic): ?>
            <li style="margin-bottom: 0.5rem;">
              <a href="tumeurVisu.php?Id-tumeur=<?= urlencode($diagnostic['Id-tumeur']); ?>" style="color: #fff; text-decoration: underline;">
                <?= htmlspecialchars($diagnostic['Id-tumeur']) ?> - <?= htmlspecialchars($diagnostic['libelle_diagnostic']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <!-- Colonne droite (optionnelle) -->
  <div class="right-col">
    <div style="text-align: center;">
      <h3 style="color: #444;">Exemple de données</h3>
      <table style="width: 100%; margin-top: 1rem; border-collapse: collapse;">
        <tr style="background: #f0eefb;">
          <td>Rayon</td>
          <td>Périmètre</td>
          <td>Lissage</td>
          <td>Concavité</td>
          <td>Symétrie</td>
          <td>Fractal-dim</td>
        </tr>
        <tr style="background: #fff;">
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
<section class="wave-two-cols" id="prediction">
  <div class="left-col">
    <div class="text-content">
      <h2>Prédiction par Image</h2>
      <p>
        Importez une image médicale (IRM, scanner...) afin d'obtenir une analyse automatique
        prédictive basée sur des modèles d'intelligence artificielle.
      </p>
    </div>
  </div>
  <div class="right-col">
    <div class="analysis-section">
      <form id="upload-form">
        <label for="file" class="upload-area" id="upload-label">
          <i class="fas fa-cloud-upload-alt"></i>
          <h4>Importez votre image</h4>
          <p>Formats acceptés: JPG, PNG</p>
          <div id="preview-container">
            <img id="preview-image" alt="Aperçu de l'image" />
          </div>
        </label>
        <input type="file" id="file" name="file" accept="image/*" />

        <div class="progress" id="upload-progress">
          <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 0%"></div>
        </div>

        <button type="button" class="btn btn-light mt-3" id="analyze-btn" onclick="analyzeImage()">
          <i class="fas fa-search me-2"></i>Analyser
        </button>
      </form>

      <div class="card mt-4" id="result-card">
        <div class="card-header">
          <h5 class="mb-0">Résultats de l'analyse</h5>
        </div>
        <div class="card-body">
          <img id="result-image" class="img-fluid mb-4" />
          <div id="result-content"></div>
        </div>
      </div>
    </div>
  </div>
</section>
 <!-- =================== SECTION PREDICTION =================== -->
 <!-- Section Prédiction -->
<section class="main" id="prediction">
  
      <div class="progress" id="upload-progress" style="display:none; height: 8px; margin-top:1rem;">
        <div class="progress-bar progress-bar-striped" style="width: 0%;"></div>
      </div>
    </div>

    <div id="result-card" class="box" style="display:none; margin-top:2rem;">
      <header>
        <h3>Résultat</h3>
      </header>
      <div id="result-content"></div>
    </div>
  </div>
</section>
<script>
  document.getElementById("file").addEventListener("change", function () {
    const preview = document.getElementById("preview-image");
    const container = document.getElementById("preview-container");
    if (this.files && this.files[0]) {
      const reader = new FileReader();
      reader.onload = (e) => {
        preview.src = e.target.result;
        container.style.display = "block";
      };
      reader.readAsDataURL(this.files[0]);
    }
  });

  async function analyzeImage() {
    const fileInput = document.getElementById("file");
    const analyzeBtn = document.getElementById("analyze-btn");
    const progressBar = document.querySelector(".progress-bar");
    const progressContainer = document.getElementById("upload-progress");

    if (!fileInput.files[0]) {
      alert("Veuillez sélectionner une image");
      return;
    }

    analyzeBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Analyse en cours...';
    analyzeBtn.disabled = true;

    progressContainer.style.display = "block";
    progressBar.style.width = "50%";

    try {
      const formData = new FormData();
      formData.append("file", fileInput.files[0]);

      const response = await fetch("http://localhost:5000/predict", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);

      const data = await response.json();

      progressBar.style.width = "100%";
      setTimeout(() => {
        progressContainer.style.display = "none";
        progressBar.style.width = "0%";
      }, 500);

      displayResult(data);
    } catch (error) {
      alert(`Erreur: ${error.message}`);
      progressContainer.style.display = "none";
      progressBar.style.width = "0%";
    } finally {
      analyzeBtn.innerHTML = '<i class="fas fa-search me-2"></i>Analyser';
      analyzeBtn.disabled = false;
    }
  }

  function displayResult(data) {
    const resultCard = document.getElementById("result-card");
    const resultContent = document.getElementById("result-content");

    const className = data.classe?.toUpperCase() || "INCONNU";
    const confidence = data.confiance?.toFixed(1) || "0.0";

    let iconClass, progressColor;
    switch (data.classe?.toLowerCase()) {
      case "benign":
        iconClass = "fa-circle-check text-success";
        progressColor = "bg-success";
        break;
      case "malignant":
        iconClass = "fa-triangle-exclamation text-danger";
        progressColor = "bg-danger";
        break;
      default:
        iconClass = "fa-circle-info text-primary";
        progressColor = "bg-primary";
    }

    resultContent.innerHTML = `
      <div class="text-center mb-3">
        <h3><i class="fas ${iconClass} me-2"></i>${className}</h3>
      </div>
      <div class="row align-items-center mb-3">
        <div class="col-12 col-md-6 text-center mb-3 mb-md-0">
          <h4 style="color: #515166;">${confidence}%</h4>
          <p class="text-muted">Confiance de l'analyse</p>
        </div>
        <div class="col-12 col-md-6">
          <div class="progress">
            <div class="progress-bar ${progressColor}" style="width: ${confidence}%;"></div>
          </div>
        </div>
      </div>
    `;

    resultCard.classList.add("show");
    resultCard.scrollIntoView({ behavior: "smooth" });
  }
</script>

           
        </div>
        <!-- fin #main -->

        <!-- Footer -->
        <footer id="footer">
           
        </footer>

    </div>
    <!-- fin #wrapper -->

    <!-- Scripts du template Stellar -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/jquery.scrollex.min.js"></script>
    <script src="assets/js/jquery.scrolly.min.js"></script>
    <script src="assets/js/browser.min.js"></script>
    <script src="assets/js/breakpoints.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>

    <!-- (Optionnel) Script JS pour fade-in #second au scroll -->
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
    </script>
</body>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const section = document.getElementById('apropos');
    const onScroll = () => {
      const rect = section.getBoundingClientRect();
      const windowHeight = window.innerHeight;
      if (rect.top < windowHeight * 0.75) {
        section.classList.add('visible');
        window.removeEventListener('scroll', onScroll); // une seule fois
      }
    };
    window.addEventListener('scroll', onScroll);
    onScroll(); // si la section est déjà visible au chargement
  });
</script>
<script>
  const checkboxes = document.querySelectorAll('.checkbox-item');
  const submitButton = document.getElementById('submit-button');

  function updateSubmitButtonState() {
    const checkedCount = document.querySelectorAll('.checkbox-item:checked').length;
    submitButton.disabled = !(checkedCount >= 1);
  }

  checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateSubmitButtonState);
  });
</script>
<script>
  document.addEventListener('scroll', function () {
    const visualSection = document.getElementById('visualisation');
    if (!visualSection) return;
    const rect = visualSection.getBoundingClientRect();
    const windowHeight = window.innerHeight || document.documentElement.clientHeight;
    if (rect.top < windowHeight * 0.75) {
      visualSection.classList.add('visible');
    }
  });
</script>


</html>
