<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Résultats de l'analyse</title>
    <link rel="stylesheet" href="style/styleStatistiques.css" type="text/css" media="screen">
</head>

<body>
    <div id="analyseResultat">
        <?php
        session_start();
        require_once 'bd.php';
        ?>
        <!-- Navigation -->
        <div class="navigation">
            <ul>
                <li><a href="exploration.php">Exploration</a></li>
                <li><a href="analyseChoix.php">Statistique</a></li>
                <li><a href="visualisation.php">Visualisation</a></li>
                <li><a href="prediction.php">Prédiction</a></li>
                <li><a href="compte.php">Compte</a></li>
            </ul>
        </div>

        <img src="img/Capture d'écran 2024-10-26 081223.png">

        <?php

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
                      JOIN diagnostic ON tumeur.`Id-tumeur` = diagnostic.`Id-tumeur` 
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

        $statsBenignes = calculerStatistiques($bdd, $caracteristiques, 1);#1 est le code pour tumeur benignes
        $statsMalignes = calculerStatistiques($bdd, $caracteristiques, 2);#2 est le code pour tumeur malignes

        echo "<h2>Analyse statistique dédiée aux caractéristiques des tumeurs</h2>";
        echo "<h3> ▶ Comparaison statistique des carctéristiques des tumeurs bénignes et malignes</h3>";
        echo "<div class='tableau1'>
        <table id='tableau' border='1'>
            <thead>
                <tr>
                    <th>Caractéristique(s)</th>";
        foreach ($caracteristiques as $carac) {
            echo "<th colspan='2'>" . htmlspecialchars($carac) . "</th>";
        }
        echo "</tr>
                <tr>
                    <th> Statistiques/Type de tumeur </th>";
        foreach ($caracteristiques as $carac) {
            echo "<th> Bénignes </th><th> Malignes </th>";
        }
        echo "</tr>
            </thead>";

        echo "<tbody>";

        $statistiques = ['count' => ' Nombre de tumeurs', 'mean' => ' Moyenne', 'stdDev' => ' Écart-type', 'min' => ' Min', 'q1' => ' Q1', 'median' => ' Médiane', 'q3' => ' Q3', 'max' => ' Max'];

        foreach ($statistiques as $key => $label) {
            echo "<tr><td>$label</td>";
            foreach ($caracteristiques as $carac) {
                $valBenigne = $statsBenignes[$carac][$key];
                $valMaligne = $statsMalignes[$carac][$key];
                echo "<td>" . (is_null($valBenigne) ? '-' : round($valBenigne, 2)) . "</td>";
                echo "<td>" . (is_null($valMaligne) ? '-' : round($valMaligne, 2)) . "</td>";
            }
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        ?>
<button class="export" onclick="exportTableToCSV('tableau_Comparaison_statistique_des_carctéristiques_des_tumeurs_bégnignes_et_malignes.csv')">Exporter la comparaison statistique des carctéristiques des tumeurs bénignes et malignes</button>


<script>
    // Fonction pour exporter le tableau en CSV
    function exportTableToCSV(filename) {
        var table = document.getElementById("tableau");
        var rows = table.querySelectorAll("tr");
        var csvContent = "";

        rows.forEach(function(row, index) {
            var cols = row.querySelectorAll("td, th");
            var rowContent = [];
            cols.forEach(function(col) {
                rowContent.push(col.innerText);
            });
            csvContent += rowContent.join(",") + "\n";
        });

        // lien pour télécharger le fichier CSV
        var hiddenElement = document.createElement('a');
        hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(csvContent);
        hiddenElement.target = '_blank';
        hiddenElement.download = filename;
        hiddenElement.click();
    }
</script>
</div>

<div id="analyseResultat2">
<?php
// Matrice de covariance pour les tumeurs bénignes
function calculerMatriceCovariance($bdd, $caracteristiques, $codeDiagnostic) {
    $query = "SELECT " . implode(", ", $caracteristiques) . " 
              FROM tumeur 
              JOIN diagnostic ON tumeur.`Id-tumeur` = diagnostic.`Id-tumeur` 
              WHERE diagnostic.`code_diagnostic` = :codeDiagnostic";
    $stmt = $bdd->prepare($query);
    $stmt->execute(['codeDiagnostic' => $codeDiagnostic]);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $n = count($data);
    $m = count($caracteristiques);

    // Calculer les moyennes
    $moyennes = array_fill(0, $m, 0);
    foreach ($data as $row) {
        for ($i = 0; $i < $m; $i++) {
            $moyennes[$i] += $row[$caracteristiques[$i]];
        }
    }
    for ($i = 0; $i < $m; $i++) {
        $moyennes[$i] /= $n;
    }

    // Calculer la matrice de covariance
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


$matriceCovarianceBenignes = calculerMatriceCovariance($bdd, $caracteristiques, 1);
$matriceCovarianceMalignes = calculerMatriceCovariance($bdd, $caracteristiques, 2);

echo "<h3> ▶ Matrice de covariance entre les caractéristiques choisies (Tumeurs bénignes)</h3>";
echo "<div class='tableau2'>
<table id='tableau-covariance-benignes' border='1'>
    <thead>
        <tr>
            <th>Caractéristique(s)</th>";
foreach ($caracteristiques as $carac) {
    echo "<th>" . htmlspecialchars($carac) . "</th>";
}
echo "</tr>
    </thead>";

echo "<tbody>";
for ($i = 0; $i < count($caracteristiques); $i++) {
    echo "<tr><td>" . htmlspecialchars($caracteristiques[$i]) . "</td>";
    for ($j = 0; $j < count($caracteristiques); $j++) {
        echo "<td>" . round($matriceCovarianceBenignes[$i][$j], 2) . "</td>";
    }
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
echo "</div>";

echo "<h3> ▶ Matrice de covariance entre les caractéristiques choisies (Tumeurs malignes)</h3>";
echo "<div class='tableau2'>
<table id='tableau-covariance-malignes' border='1'>
    <thead>
        <tr>
            <th>Caractéristique(s)</th>";
foreach ($caracteristiques as $carac) {
    echo "<th>" . htmlspecialchars($carac) . "</th>";
}
echo "</tr>
    </thead>";

echo "<tbody>";
for ($i = 0; $i < count($caracteristiques); $i++) {
    echo "<tr><td>" . htmlspecialchars($caracteristiques[$i]) . "</td>";
    for ($j = 0; $j < count($caracteristiques); $j++) {
        echo "<td>" . round($matriceCovarianceMalignes[$i][$j], 2) . "</td>";
    }
    echo "</tr>";
}
echo "</tbody>";
echo "</table>";
echo "</div>";

?>

<button class="export" onclick="exportTablesToCSV('Matrices_de_covariance.csv')">Exporter les matrices en CSV</button>

<script>
    // Fonction pour exporter les deux tableaux en un seul fichier CSV
    function exportTablesToCSV(filename) {
        var tables = [document.getElementById("tableau-covariance-benignes"), document.getElementById("tableau-covariance-malignes")];
        var csvContent = "";

        tables.forEach(function(table) {
            var rows = table.querySelectorAll("tr");
            rows.forEach(function(row) {
                var cols = row.querySelectorAll("td, th");
                var rowContent = [];
                cols.forEach(function(col) {
                    rowContent.push(col.innerText);
                });
                csvContent += rowContent.join(",") + "\n";
            });
            csvContent += "\n"; // Ajouter une ligne vide entre les tableaux
        });

        // Créer un lien pour télécharger le fichier CSV
        var hiddenElement = document.createElement('a');
        hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(csvContent);
        hiddenElement.target = '_blank';
        hiddenElement.download = filename;
        hiddenElement.click();
    }
</script>
</div>
</body>

</html>
