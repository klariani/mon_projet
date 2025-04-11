<?php
require "./getBd.php";
$bdd = getBD();
session_start();

if (!isset($_POST['caracteristiques']) || count($_POST['caracteristiques']) < 1) {
    echo "<p>Veuillez choisir au moins 1 caractéristique.</p>";
    echo '<a href="analyseChoix.php">Retour</a>';
    exit;
}

$caracteristiques = $_POST['caracteristiques'];

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
  <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
  <div id="wrapper">
    <header id="header" class="alt">
      <h1>Résultats de l'analyse</h1>
      <p>Statistiques descriptives et matrices de covariance</p>
    </header>

    <div id="main">
      <section class="main special">
        <div class="inner" id="analyseResultat">
          <h2>Comparaison Statistique</h2>
          <div class="table-wrapper">
            <table class="alt">
              <thead>
                <tr><th>Stat/Type</th>
                  <?php foreach ($caracteristiques as $carac): ?>
                    <th><?= htmlspecialchars($carac) ?> (B)</th>
                    <th><?= htmlspecialchars($carac) ?> (M)</th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php
                $statistiques = ['count'=>'Count', 'mean'=>'Moyenne', 'stdDev'=>'Écart-type', 'min'=>'Min', 'q1'=>'Q1', 'median'=>'Médiane', 'q3'=>'Q3', 'max'=>'Max'];
                foreach ($statistiques as $key => $label):
                  echo "<tr><td>$label</td>";
                  foreach ($caracteristiques as $carac) {
                    echo "<td>" . round($statsBenignes[$carac][$key], 2) . "</td><td>" . round($statsMalignes[$carac][$key], 2) . "</td>";
                  }
                  echo "</tr>";
                endforeach;
                ?>
              </tbody>
            </table>
          </div>

          <h2>Matrice de covariance (Bénignes)</h2>
          <div class="table-wrapper">
          <table class="alt" id="tableau-covariance-benignes">
              <thead>
                <tr><th></th>
                <?php foreach ($caracteristiques as $c) echo "<th>$c</th>"; ?>
                </tr>
              </thead>
              <tbody>
              <?php for ($i = 0; $i < count($caracteristiques); $i++): ?>
                <tr>
                  <td><?= $caracteristiques[$i] ?></td>
                  <?php for ($j = 0; $j < count($caracteristiques); $j++): ?>
                    <td><?= round($matriceCovarianceBenignes[$i][$j], 2) ?></td>
                  <?php endfor; ?>
                </tr>
              <?php endfor; ?>
              </tbody>
            </table>
          </div>

          <h2>Matrice de covariance (Malignes)</h2>
          <div class="table-wrapper">
          <table class="alt" id="tableau-covariance-malignes">
              <thead>
                <tr><th></th>
                <?php foreach ($caracteristiques as $c) echo "<th>$c</th>"; ?>
                </tr>
              </thead>
              <tbody>
              <?php for ($i = 0; $i < count($caracteristiques); $i++): ?>
                <tr>
                  <td><?= $caracteristiques[$i] ?></td>
                  <?php for ($j = 0; $j < count($caracteristiques); $j++): ?>
                    <td><?= round($matriceCovarianceMalignes[$i][$j], 2) ?></td>
                  <?php endfor; ?>
                </tr>
              <?php endfor; ?>
              </tbody>
            </table>
          </div>

          

          <button class="export" onclick="exportTablesToCSV('Matrices_de_covariance.csv')">Exporter les matrices en CSV</button>

<script>
  function exportTablesToCSV(filename) {
    const tableIds = ["tableau-covariance-benignes", "tableau-covariance-malignes"];
    let csvContent = "";

    tableIds.forEach(id => {
      const table = document.getElementById(id);
      if (!table) return;

      const rows = table.querySelectorAll("tr");
      rows.forEach(row => {
        const cols = row.querySelectorAll("th, td");
        const rowData = Array.from(cols).map(col => `"${col.innerText.trim()}"`);
        csvContent += rowData.join(",") + "\n";
      });
      csvContent += "\n";
    });

    if (!csvContent.trim()) {
      alert("Aucune donnée à exporter.");
      return;
    }

    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
</script>

</body>
</html>
