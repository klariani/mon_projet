<?php
// Démarrer la session si besoin
session_start();
// Inclure une configuration ou une vérification si nécessaire
// include('config.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Analyse</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/main.css">
  <style>
    /* === STYLES PERSONNALISÉS === */
    .slider {
      max-width: 100%;
      margin: 2rem auto;
      position: relative;
      overflow: hidden;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    }
    .slides {
      display: flex;
      transition: transform 0.5s ease-in-out;
    }
    .slide {
      width: 100%;
      height: 600px;
      border: none;
      flex-shrink: 0;
    }
    .navigation {
      text-align: center;
      margin-top: 1rem;
    }
    .nav-btn {
      display: inline-block;
      cursor: pointer;
      width: 12px;
      height: 12px;
      background: #999;
      border-radius: 50%;
      margin: 0 5px;
    }
    .nav-btn.active {
      background: #7f71c6;
    }
    #description-container, #biopsies-description-container {
      max-width: 800px;
      margin: 2rem auto;
      font-size: 1rem;
      color: #333;
      padding: 1rem;
      background: #f9f9f9;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .box-container {
      display: flex;
      justify-content: space-around;
      margin-top: 2rem;
    }
    .box {
      width: 45%;
      padding: 20px;
      background: #f4f4f4;
      border: 2px solid #ccc;
      border-radius: 10px;
      text-align: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .box:hover, .box.active {
      background-color:rgb(228, 154, 232);
      color: white;
    }
    .hidden {
      display: none;
    }
  </style>
</head>
<body class="is-preload">
  <div id="wrapper">
    <header id="header" class="alt">
      <h1>Oncoanalyse</h1>
      <p>Analyse exploratoire des données</p>
    </header>

    <nav id="nav">
      <ul>
        <li><a href="exploration.php">Exploration</a></li>
        <li><a href="analyse.php" class="active">Statistique</a></li>
        <li><a href="visualisation.php">Visualisation</a></li>
        <li><a href="prediction.php">Prédiction</a></li>
        <li><a href="login.php">Compte</a></li>
      </ul>
    </nav>

    <div id="main">
      <section class="main special" id="analyse">
        <div class="inner">
          <header class="major">
          <h2>Analyse</h2>
<p>Nous nous sommes appuyés sur trois jeux de données indépendants pour cette analyse :</p>
<ul>
  <li><strong>Jeu de données sur les biopsies :</strong> Contient des informations détaillées sur les tumeurs, incluant leur type (bénignes ou malignes) et des données diagnostiques.</li>
  <li><strong>Jeu de données sur les caractéristiques des patientes :</strong> Inclut des informations biométriques et sanguines, telles que l'âge, le BMI (indice de masse corporelle), les niveaux de glucose, d'insuline, etc.</li>
  <li><strong>Jeu de données sur les images médicales :</strong> Contient des images de tumeurs utilisées pour des analyses visuelles et de détection.</li>
</ul>
<p>Cette analyse exploratoire se concentre sur les deux premiers jeux de données, à savoir les biopsies et les caractéristiques biométriques/sanguines des patientes.</p>

          </header>
        </div>

        <!-- Choix entre les différents jeux de données -->
        <div class="box-container">
          <div id="biopsies-box" class="box" onclick="showBiopsies()">
            <h3>Données  de Biopsies</h3>
          </div>
          <div id="patientes-box" class="box" onclick="showPatientes()">
            <h3>Données Biométriques et Sanguines</h3>
          </div>
        </div>

        <!-- SLIDER DONNÉES BIOMÉTRIQUES/SANGUINES -->
        <div id="slider-container">
          <div class="slider">
            <div class="slides" id="slide-track">
              <iframe src="img/boxplot_par_diagnostic.html" class="slide"></iframe>
              <iframe src="img/matrice_correlation.html" class="slide"></iframe>
              <iframe src="img/zoomable_sunburst.html" class="slide"></iframe>
              <iframe src="img/correlation_barplot.html" class="slide"></iframe>
            </div>
          </div>
          <div class="navigation" id="navigation-dots"></div>
          <div id="description-container">
            <p>Sélectionnez une visualisation pour en afficher la description.</p>
          </div>
        </div>

        <!-- SLIDER BIOPSIES -->
        <div id="biopsies-container" class="hidden">
          <h3>Analyse des Biopsies</h3>
          <div class="slider">
            <div class="slides" id="biopsies-slide-track">
              <iframe src="img/pca_interactif_avec_cercles.html" class="slide"></iframe>
              <iframe src="img/boxplot_aire_tumeur.html" class="slide"></iframe>
            </div>
          </div>
          <div class="navigation" id="biopsies-navigation-dots"></div>
          <div id="biopsies-description-container">
            <p>Sélectionnez une visualisation pour en afficher la description.</p>
          </div>
        </div>
      </section>
    </div>

    <footer id="footer">
      <p>&copy; Oncoanalyse. Tous droits réservés.</p>
    </footer>
  </div>

  <script>
    // Fonctionnalité de navigation dans les sliders (données biométriques et biopsies)
    const slides = document.getElementById("slide-track");
    const slideFrames = slides.querySelectorAll(".slide");
    const navigation = document.getElementById("navigation-dots");

    const biopsiesSlides = document.getElementById("biopsies-slide-track");
    const biopsiesSlideFrames = biopsiesSlides.querySelectorAll(".slide");
    const biopsiesNavigation = document.getElementById("biopsies-navigation-dots");

    // Descriptions des visualisations
    const descriptions = {
      "img/boxplot_par_diagnostic.html": "Boxplot de la distribution des variables en fonction du diagnostic.",
      "img/matrice_correlation.html": "Matrice de corrélation des variables.",
      "img/zoomable_sunburst.html": "Visualisation interactive des données biométriques et sanguines | Diagramme sunburst.",
      "img/correlation_barplot.html": "Diagramme en barres illustrant la corrélation entre chaque variable et la variable choisie."
    };
    const biopsiesDescriptions = {
      "img/pca_interactif_avec_cercles.html": "PCA interactif avec cercles.",
      "img/boxplot_aire_tumeur.html": "Boxplot de l'aire des tumeurs."
    };

    // Création des points de navigation pour les sliders
    function createNavDots(frames, container, callback) {
      container.innerHTML = "";
      frames.forEach((_, i) => {
        const dot = document.createElement("span");
        dot.className = "nav-btn";
        dot.onclick = () => callback(i);
        container.appendChild(dot);
      });
    }

    // Fonction pour afficher un slide spécifique
    function showSlide(index) {
      slides.style.transform = `translateX(-${index * 100}%)`;
      document.querySelectorAll("#navigation-dots .nav-btn").forEach((btn, i) => {
        btn.classList.toggle("active", i === index);
      });
      const file = slideFrames[index].getAttribute("src");
      document.getElementById("description-container").innerText = descriptions[file] || "";
    }

    // Fonction pour afficher un slide spécifique pour les biopsies
    function showBiopsiesSlide(index) {
      biopsiesSlides.style.transform = `translateX(-${index * 100}%)`;
      document.querySelectorAll("#biopsies-navigation-dots .nav-btn").forEach((btn, i) => {
        btn.classList.toggle("active", i === index);
      });
      const file = biopsiesSlideFrames[index].getAttribute("src");
      document.getElementById("biopsies-description-container").innerText = biopsiesDescriptions[file] || "";
    }

    // Affichage des sections (biopsies ou biométriques/sanguines)
    function showPatientes() {
      document.getElementById("slider-container").style.display = "block";
      document.getElementById("biopsies-container").style.display = "none";
      document.getElementById("patientes-box").classList.add("active");
      document.getElementById("biopsies-box").classList.remove("active");
    }

    function showBiopsies() {
      document.getElementById("slider-container").style.display = "none";
      document.getElementById("biopsies-container").style.display = "block";
      document.getElementById("biopsies-box").classList.add("active");
      document.getElementById("patientes-box").classList.remove("active");
    }

    // Initialisation des sliders et des descriptions
    createNavDots(slideFrames, navigation, showSlide);
    createNavDots(biopsiesSlideFrames, biopsiesNavigation, showBiopsiesSlide);
    showSlide(0);
    showBiopsiesSlide(0);
    showPatientes(); // vue par défaut
  </script>
</body>
</html>
