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
    <section class="main special" id="formulaire-tumeur">
  <div class="inner">
    <header class="major">
      <h2>Entrer les données morphologiques d'une tumeur</h2>
      <p>Complétez les champs pour ajouter les données d’une tumeur à analyser.</p>
    </header>

    <style>
      #formulaire-tumeur form {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
      }

      #formulaire-tumeur .form-column {
        flex: 1;
        min-width: 300px;
      }

      #formulaire-tumeur label {
        font-weight: 600;
        display: block;
        margin-top: 10px;
      }

      #formulaire-tumeur input {
        width: 100%;
        padding: 10px 12px;
        margin-top: 6px;
        margin-bottom: 12px;
        border-radius: 8px;
        border: none;
        outline: none;
        font-size: 1rem;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      #formulaire-tumeur input:focus {
        outline: 2px solid #d4c2ff;
        background: #fdfdfd;
      }

      #formulaire-tumeur button {
        margin-top: 2rem;
        background-color: #ffffff;
        color: #6a4fbf;
        font-weight: bold;
        border: none;
        padding: 1rem;
        width: 100%;
        border-radius: 12px;
        cursor: pointer;
        transition: background 0.3s ease;
      }

      #formulaire-tumeur button:hover {
        background-color: #eae2ff;
      }

      #formulaire-tumeur .form-container {
        background: linear-gradient(135deg, #e2a8e6, #a0c4ff);
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        color: #fff;
        max-width: 1000px;
        margin: 0 auto;
      }
    </style>

    <div class="form-container">
      <form action="traitement.php" method="post">
        <div class="form-column">
          
          <label for="rayon">Rayon moyen</label>
          <input type="number" step="0.0001" name="rayon" id="rayon" required>

          <label for="texture">Texture moyenne</label>
          <input type="number" step="0.0001" name="texture" id="texture" required>

          <label for="perimetre">Périmètre moyen</label>
          <input type="number" step="0.0001" name="perimetre" id="perimetre" required>

          <label for="air">Air moyenne</label>
          <input type="number" step="0.0001" name="air" id="air" required>

          <label for="uniformite">Uniformité moyenne</label>
          <input type="number" step="0.0001" name="uniformite" id="uniformite" required>
        </div>

        <div class="form-column">
          <label for="compact">Compacité moyenne</label>
          <input type="number" step="0.0001" name="compact" id="compact" required>

          <label for="concavite">Concavité moyenne</label>
          <input type="number" step="0.0001" name="concavite" id="concavite" required>

          <label for="nconcavite">Nb. Concavités moyennes</label>
          <input type="number" step="0.0001" name="nconcavite" id="nconcavite" required>

          <label for="symetrie">Symétrie moyenne</label>
          <input type="number" step="0.0001" name="symetrie" id="symetrie" required>

          <label for="dimfractal">Dim. fractale moyenne</label>
          <input type="number" step="0.0001" name="dimfractal" id="dimfractal" required>

          <button type="submit">Soumettre</button>
        </div>
      </form>
    </div>
  </div>
</section>


    </div>

    <footer id="footer">
      <p>&copy; Oncoanalyse. Tous droits réservés.</p>
    </footer>
  </div>
</body>
</html>
