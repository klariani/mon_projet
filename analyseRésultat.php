<?php
session_start();
require "getBd.php";

if (!isset($_POST['caracteristiques']) || count($_POST['caracteristiques']) < 1) {
    echo "<p>Veuillez choisir au moins 1 caractéristique.</p>";
    echo '<a href="analyseChoix.php">Retour</a>';
    exit;
}

$caracteristiques = $_POST['caracteristiques'];
$bdd = getBD();

function calculerStatistiques($bdd, $caracteristiques, $codeDiagnostic)
{
    $query = "SELECT " . implode(", ", $caracteristiques) . " 
              FROM tumeur 
              JOIN diagnostic ON tumeur.`code_diagnostic` = diagnostic.`code_diagnostic`
              WHERE diagnostic.`code_diagnostic` = :codeDiagnostic";
    $stmt = $bdd->prepare($query);
    $stmt->execute(['codeDiagnostic' => $codeDiagnostic]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stats = [];

    foreach ($caracteristiques as $carac) {
        $values = array_column($data, $carac);
        $values = array_map('floatval', $values);

        sort($values);
        $count = count($values);
        $mean = $count > 0 ? array_sum($values) / $count : 0;
        $stdDev = $count > 1 ? sqrt(array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / ($count - 1)) : 0;

        $min = $count > 0 ? min($values) : null;
        $max = $count > 0 ? max($values) : null;
        $median = $count > 0 ? $values[floor(($count - 1) / 2)] : null;
        $q1 = $count > 1 ? $values[floor(($count - 1) / 4)] : null;
        $q3 = $count > 1 ? $values[floor(3 * ($count - 1) / 4)] : null;

        $stats[$carac] = [
            'count' => $count,
            'mean' => $mean,
            'stdDev' => $stdDev,
            'min' => $min,
            'max' => $max,
            'median' => $median,
            'q1' => $q1,
            'q3' => $q3,
        ];
    }

    return $stats;
}

function calculerMatriceCovariance($bdd, $caracteristiques, $codeDiagnostic) {
    $query = "SELECT " . implode(", ", $caracteristiques) . " 
              FROM tumeur 
              JOIN diagnostic ON tumeur.`code_diagnostic` = diagnostic.`code_diagnostic`
              WHERE diagnostic.`code_diagnostic` = :codeDiagnostic";
    $stmt = $bdd->prepare($query);
    $stmt->execute(['codeDiagnostic' => $codeDiagnostic]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $n = count($data);
    $m = count($caracteristiques);

    $moyennes = array_fill(0, $m, 0);
    foreach ($data as $row) {
        for ($i = 0; $i < $m; $i++) {
            $moyennes[$i] += $row[$caracteristiques[$i]];
        }
    }
    for ($i = 0; $i < $m; $i++) {
        $moyennes[$i] /= $n;
    }

    $covariance = array_fill(0, $m, array_fill(0, $m, 0));
    foreach ($data as $row) {
        for ($i = 0; $i < $m; $i++) {
            for ($j = 0; $j < $m; $j++) {
                $covariance[$i][$j] += ($row[$caracteristiques[$i]] - $moyennes[$i]) * ($row[$caracteristiques[$j]] - $moyennes[$j]);
            }
        }
    }
    for ($i = 0; $i < $m; $i++) {
        for ($j = 0; $j < $m; $j++) {
            $covariance[$i][$j] /= ($n - 1);
        }
    }

    return $covariance;
}

$statsBenignes = calculerStatistiques($bdd, $caracteristiques, 1);
$statsMalignes = calculerStatistiques($bdd, $caracteristiques, 2);
$matriceCovarianceBenignes = calculerMatriceCovariance($bdd, $caracteristiques, 1);
$matriceCovarianceMalignes = calculerMatriceCovariance($bdd, $caracteristiques, 2);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Analyse Statistique</title>
  <link rel="stylesheet" href="assets/css/main.css" />
</head>
<body class="is-preload">
  <div id="wrapper">
    <header id="header">
      <h1>Analyse Statistique</h1>
      <p>Comparaison des caractéristiques des tumeurs</p>
    </header>
    <nav id="nav">
      <ul>
        <li><a href="index.php">Exploration</a></li>
        <li><a href="index.php" class="active">Statistique</a></li>
        <li><a href="index.php">Visualisation</a></li>
        <li><a href="index.php">Prédiction</a></li>
        <li><a href="index.php">Compte</a></li>
      </ul>
    </nav>
    <div id="main" class="main">
      <section class="main">
        <header class="major">
          <h2>Comparaison statistique des tumeurs</h2>
        </header>
        <div class="table-wrapper">
          <table class="alt">
            <thead>
              <tr>
                <th>Statistiques / Caractéristiques</th>
                <?php foreach ($caracteristiques as $carac): ?>
                  <th><?= htmlspecialchars($carac) ?> (B)</th>
                  <th><?= htmlspecialchars($carac) ?> (M)</th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php
              $statistiques = ['count' => 'Nombre', 'mean' => 'Moyenne', 'stdDev' => 'Écart-type', 'min' => 'Min', 'q1' => 'Q1', 'median' => 'Médiane', 'q3' => 'Q3', 'max' => 'Max'];
              foreach ($statistiques as $key => $label): ?>
                <tr>
                  <td><?= $label ?></td>
                  <?php foreach ($caracteristiques as $carac): ?>
                    <td><?= round($statsBenignes[$carac][$key], 2) ?></td>
                    <td><?= round($statsMalignes[$carac][$key], 2) ?></td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="table-wrapper">
          <h3>Matrice de covariance (Tumeurs Bénignes)</h3>
          <table class="alt">
            <thead>
              <tr><th></th>
              <?php foreach ($caracteristiques as $carac): ?>
                <th><?= htmlspecialchars($carac) ?></th>
              <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php for ($i = 0; $i < count($caracteristiques); $i++): ?>
                <tr>
                  <td><?= htmlspecialchars($caracteristiques[$i]) ?></td>
                  <?php for ($j = 0; $j < count($caracteristiques); $j++): ?>
                    <td><?= round($matriceCovarianceBenignes[$i][$j], 2) ?></td>
                  <?php endfor; ?>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>

          <h3>Matrice de covariance (Tumeurs Malignes)</h3>
          <table class="alt">
            <thead>
              <tr><th></th>
              <?php foreach ($caracteristiques as $carac): ?>
                <th><?= htmlspecialchars($carac) ?></th>
              <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php for ($i = 0; $i < count($caracteristiques); $i++): ?>
                <tr>
                  <td><?= htmlspecialchars($caracteristiques[$i]) ?></td>
                  <?php for ($j = 0; $j < count($caracteristiques); $j++): ?>
                    <td><?= round($matriceCovarianceMalignes[$i][$j], 2) ?></td>
                  <?php endfor; ?>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
        <ul class="actions">
          <li><button class="button" onclick="exportCSV()">Exporter en CSV</button></li>
        </ul>
      </section>
    </div>
  </div>

  <script>
    function exportCSV() {
      const tables = document.querySelectorAll('table');
      let csv = '';
      tables.forEach(table => {
        const rows = table.querySelectorAll('tr');
        rows.forEach(row => {
          const cols = row.querySelectorAll('td, th');
          let rowData = [];
          cols.forEach(col => rowData.push(col.innerText));
          csv += rowData.join(',') + '\n';
        });
        csv += '\n';
      });

      const blob = new Blob([csv], { type: 'text/csv' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'analyse_statistique.csv';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  </script>
</body>
</html>
