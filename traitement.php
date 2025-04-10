
<?php
require "../getBd.php";
$bdd = getBD();
session_start();

$query = $bdd->prepare("SELECT tumeur.`Id-tumeur`, diagnostic.libelle_diagnostic FROM tumeur, diagnostic WHERE tumeur.code_diagnostic = diagnostic.code_diagnostic");
$query->execute();
$diagnostics = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Visualisation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/main.css">
  <style>
    .scrollable-list {
      max-height: 300px;
      overflow-y: auto;
      padding: 0.5rem 1rem;
      margin: 0 auto 2rem;
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.03);
      max-width: 600px;
    }
    .actions-list {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    .actions-list a.button.small {
      display: block;
      padding: 0.5rem 0.75rem;
      background: #7f71c6;
      color: #fff;
      text-align: center;
      border-radius: 6px;
      font-size: 0.85rem;
      text-decoration: none;
      transition: background 0.3s ease;
    }
    .actions-list a.button.small:hover {
      background: #6e5bb8;
    }
    #search-input {
      max-width: 600px;
      margin: 1rem auto;
      display: block;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
  </style>
</head>
<body class="is-preload">
  <div id="wrapper">
    <header id="header" class="alt">
      <h1>Oncoanalyse</h1>
      <p>Visualisation des données d'une tumeur</p>
    </header>

    <nav id="nav">
      <ul>
        <li><a href="index.php">Exploration</a></li>
        <li><a href="index.php">Statistique</a></li>
        <li><a href="index.php" class="active">Visualisation</a></li>
        <li><a href="index.php">Prédiction</a></li>
        <li><a href="index.php">Compte</a></li>
      </ul>
    </nav>

    <div id="main">
      <section class="main special" id="visualisation">
        <div class="inner">
          <header class="major">
            <h2>Visualisation d'une Tumeur</h2>
            <p>Choisissez une tumeur pour voir ses détails et une visualisation graphique.</p>
          </header>

          <input type="text" id="search-input" placeholder="Rechercher un identifiant...">

          <div class="scrollable-list">
            <div class="actions-list" id="id-list">
			<li><a href="tumeurVisu2.php">Insérer vos données</a></li>
              <?php foreach ($diagnostics as $diagnostic): ?>
                <a class="button small" href="tumeurVisu.php?Id-tumeur=<?php echo urlencode($diagnostic['Id-tumeur']); ?>">
                  <?= htmlspecialchars($diagnostic['Id-tumeur']) ?> - <?= htmlspecialchars($diagnostic['libelle_diagnostic']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <script>
            const searchInput = document.getElementById('search-input');
            const idList = document.getElementById('id-list');
            const links = idList.getElementsByTagName('a');

            searchInput.addEventListener('input', function () {
              const filter = this.value.toLowerCase();
              for (let i = 0; i < links.length; i++) {
                const txt = links[i].textContent.toLowerCase();
                links[i].style.display = txt.includes(filter) ? 'block' : 'none';
              }
            });
          </script>

          <?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Vérification de la présence de toutes les données et qu'elles sont valides
    if (isset($_POST['rayon'], $_POST['texture'], $_POST['perimetre'], $_POST['air'], $_POST['uniformite'],
              $_POST['compact'], $_POST['concavite'], $_POST['nconcavite'], $_POST['symetrie'], $_POST['dimfractal'])) {

        // Récupération et validation des données
        $rayon = filter_var($_POST['rayon'], FILTER_VALIDATE_FLOAT);
        $texture = filter_var($_POST['texture'], FILTER_VALIDATE_FLOAT);
        $perimetre = filter_var($_POST['perimetre'], FILTER_VALIDATE_FLOAT);
        $air = filter_var($_POST['air'], FILTER_VALIDATE_FLOAT);
        $uniformite = filter_var($_POST['uniformite'], FILTER_VALIDATE_FLOAT);
        $compact = filter_var($_POST['compact'], FILTER_VALIDATE_FLOAT);
        $concavite = filter_var($_POST['concavite'], FILTER_VALIDATE_FLOAT);
        $nconcavite = filter_var($_POST['nconcavite'], FILTER_VALIDATE_FLOAT);
        $symetrie = filter_var($_POST['symetrie'], FILTER_VALIDATE_FLOAT);
        $dimfractal = filter_var($_POST['dimfractal'], FILTER_VALIDATE_FLOAT);

        // Vérification si toutes les données sont valides
        if ($rayon === false || $texture === false || $perimetre === false || $air === false ||
            $uniformite === false || $compact === false || $concavite === false || $nconcavite === false ||
            $symetrie === false || $dimfractal === false) {
            echo "❌ Erreur : Certaines données sont invalides. Veuillez vérifier les valeurs numériques.";
            exit; // Arrête le script si des données sont invalides
        }

        // Si toutes les données sont valides, on prépare les données à envoyer
        $data = [
            "rayon_moyen" => $rayon,
            "texture_moyenne" => $texture,
            "perimetre_moyen" => $perimetre,
            "air_moyenne" => $air,
            "uniformite_moyenne" => $uniformite,
            "compact_moyen" => $compact,
            "concavite_moyenne" => $concavite,
            "nconcavite_moyenne" => $nconcavite,
            "symetrie_moyenne" => $symetrie,
            "dim_fractal_moyenne" => $dimfractal
        ];

        $jsonData = json_encode($data);

        // Envoi des données à l'API (exemple avec cURL)
        $ch = curl_init('http://127.0.0.1:8000/predict/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            echo "Erreur cURL: " . curl_error($ch);
        } else {
            $result = json_decode($response, true);
            echo "<h2>Résultats de la Prédiction</h2>";
            if (isset($result['diagnostic'])) {
                echo "<p><strong>Diagnostic :</strong> " . $result['diagnostic'] . "</p>";
                echo "<p><strong>Confiance :</strong> " . $result['confiance'] . "</p>";
            } else {
                echo "<p>❌ Erreur : " . htmlspecialchars(json_encode($result)) . "</p>";
            }
        }

        curl_close($ch);
    } else {
        // Si des données sont manquantes
        echo "❌ Erreur : Certaines données sont manquantes.";
    }
}
$python = 'C:/Users/mayss/AppData/Local/Programs/Python/Python313/python.exe';
              $script = 'C:/MAMP/htdocs/GestionP/mon_projet/python/testvisu.py';
              $command = "$python $script 2>&1";
              shell_exec($command);

              echo '<iframe src="tmp/graph.html" width="100%" height="600" style="border:none; margin-top:2rem;"></iframe>';
?>
        </div>
      </section>
    </div>

    <footer id="footer">
      <p>&copy; Oncoanalyse. Tous droits réservés.</p>
    </footer>
  </div>
</body>
</html>
