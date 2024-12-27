<?php
session_start();
require "./getBd.php";
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Analyse statistique</title>
    <link rel="stylesheet" href="./style/styleStatistique.css" type="text/css" media="screen">
    <link href="https://fonts.googleapis.com/css2?family=Kaisei+HarunoUmi&display=swap" rel="stylesheet">
	
</head>

<body>
    <!-- Navigation -->
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

    <?php
    $bdd = getBD();

    // Récupérer toutes les colonnes de la table sauf 'Id-tumeur'
    $query = $bdd->query("SHOW COLUMNS FROM tumeur");
    $caracteristiques = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Field'] != 'Id-tumeur') {
            $caracteristiques[] = $row['Field'];
        }
    }
    ?>

    <div class="container">
        <h2>Analyse Statistique des Tumeurs</h2>
        <p>Sélectionnez <strong>au moins 1 caractéristique</strong> à analyser :</p>

        <!--liste choix des caractéristiques-->
        <form method="post" action="analyseRésultat.php">
            <div class="checkbox-container">
                <div class="checkbox-scroll">
                    <?php foreach ($caracteristiques as $carac): ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="caracteristiques[]" value="<?php echo htmlspecialchars($carac); ?>" class="checkbox-item">
                            <?php echo htmlspecialchars($carac); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="button-container">
                <button type="submit" id="submit-button" disabled>Analyser</button>
            </div>
        </form>
    </div>

    <script>
        // Sélectionne toutes les cases à cocher et le bouton
        const checkboxes = document.querySelectorAll('.checkbox-item');
        const submitButton = document.getElementById('submit-button');

        // Met à jour l'état du bouton (il faut au moins une case cochée)
        function updateSubmitButtonState() {
            const checkedCount = document.querySelectorAll('.checkbox-item:checked').length;
            submitButton.disabled = !(checkedCount >= 1 ); // Active seulement si entre 1 et 4
        }

        // Ajoute un événement 'change' sur chaque case à cocher
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSubmitButtonState);
        });
    </script>
</body>

</html>
