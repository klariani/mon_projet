<?php
require "getBd.php";
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
          if (isset($_GET['Id-tumeur'])) {
            $Id_tumeur = intval($_GET['Id-tumeur']);
            $requete = $bdd->prepare('SELECT * FROM tumeur WHERE `Id-tumeur` = ?');
            $requete->execute([$Id_tumeur]);
            $tumeur = $requete->fetch();

            if ($tumeur) {
              $tempDir = __DIR__ . '/tmp';
              $tempFile = $tempDir . '/test.json';
              if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

              $tumeurData = [
                "rayon_moyen" => $tumeur['rayon_moyen'],
                "perimetre_moyen" => $tumeur['perimetre_moyen'],
                "uniformite_moyenne" => $tumeur['uniformite_moyenne'],
                "concavite_moyenne" => $tumeur['concavite_moyenne'],
                "symetrie_moyenne" => $tumeur['symetrie_moyenne'],
                "dim_fractal_moyenne" => $tumeur['dim_fractal_moyenne']
              ];

              file_put_contents($tempFile, json_encode($tumeurData, JSON_PRETTY_PRINT));

              $python = 'C:/Users/mayss/AppData/Local/Programs/Python/Python313/python.exe';
              $script = 'C:/MAMP/htdocs/GestionP/Test-template/testvisu.py';
              $command = "$python $script 2>&1";
              shell_exec($command);

              echo '<iframe src="tmp/graph.html" width="100%" height="600" style="border:none; margin-top:2rem;"></iframe>';

              echo '<div class="table-wrapper">';
              echo '<table class="alt">';
              echo '<thead><tr><th>Rayon</th><th>Périmètre</th><th>Lissage</th><th>Concavité</th><th>Symétrie</th><th>Fractal-dim</th></tr></thead>';
              echo '<tbody><tr>';
              echo '<td>' . htmlspecialchars($tumeur['rayon_moyen']) . '</td>';
              echo '<td>' . htmlspecialchars($tumeur['perimetre_moyen']) . '</td>';
              echo '<td>' . htmlspecialchars($tumeur['uniformite_moyenne']) . '</td>';
              echo '<td>' . htmlspecialchars($tumeur['concavite_moyenne']) . '</td>';
              echo '<td>' . htmlspecialchars($tumeur['symetrie_moyenne']) . '</td>';
              echo '<td>' . htmlspecialchars($tumeur['dim_fractal_moyenne']) . '</td>';
              echo '</tr></tbody></table>';
              echo '</div>';
            } else {
              echo "<p>Aucune tumeur trouvée avec cet ID.</p>";
            }
          } else {
            echo "<p>ID de tumeur non spécifié dans l'URL.</p>";
          }
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