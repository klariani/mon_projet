<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Visualisation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/main.css">
  <style>
    .slider {
      max-width: 800px;
      margin: 2rem auto;
      position: relative;
      overflow: hidden;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.3);
    }
    .slides {
      display: flex;
      transition: transform 0.5s ease-in-out;
    }
    .slide {
      min-width: 100%;
      height: 500px;
      border: none;
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
    #description-container {
      max-width: 800px;
      margin: 2rem auto;
      font-size: 1rem;
      color: #333;
      padding: 1rem;
      background: #f9f9f9;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
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
        <li><a href="exploration.php">Exploration</a></li>
        <li><a href="analyseChoix.php">Statistique</a></li>
        <li><a href="visualisation.php" class="active">Visualisation</a></li>
        <li><a href="prediction.php">Prédiction</a></li>
        <li><a href="login.php">Compte</a></li>
      </ul>
    </nav>

    <div id="main">
      <section class="main special" id="visualisation">
        <div class="inner">
          <header class="major">
            <h2>Visualisation d'une Tumeur</h2>
            <p>Le projet <strong>Oncoanalyse</strong> s’appuie sur une base de données médicales regroupant les mesures de caractéristiques morphologiques de tumeurs du sein...</p>
          </header>
        </div>
      </section>

      <!-- SLIDER INTERACTIF -->
      <div class="slider">
        <div class="slides" id="slide-track">
          <iframe src="img/pca_interactif.html" class="slide"></iframe>
          <iframe src="img/boxplot_interactif.html" class="slide"></iframe>
        </div>
      </div>

      <div class="navigation" id="navigation-dots"></div>

      <!-- Description dynamique -->
      <div id="description-container">
        <p>Sélectionnez une visualisation pour en afficher la description.</p>
      </div>
    </div>

    <footer id="footer">
      <p>&copy; Oncoanalyse. Tous droits réservés.</p>
    </footer>
  </div>

  <script>
    let currentSlide = 0;
    const slides = document.getElementById("slide-track");
    const slideFrames = slides.querySelectorAll(".slide");
    const navigation = document.getElementById("navigation-dots");

    function showSlide(index) {
      currentSlide = index;
      const offset = -index * 100;
      slides.style.transform = `translateX(${offset}%)`;

      document.querySelectorAll(".nav-btn").forEach((btn, i) => {
        btn.classList.toggle("active", i === index);
      });

      const iframe = slideFrames[index];
      if (iframe) {
        const filePath = iframe.getAttribute("src");
        fetchDescription(filePath);
      }
    }

    function fetchDescription(filePath) {
      fetch('description_chatgpt.php?image_path=' + encodeURIComponent(filePath))
        .then(response => response.json())
        .then(data => {
          document.getElementById("description-container").innerText = data.description;
        })
        .catch(error => console.error('Erreur:', error));
    }

    // Création automatique des boutons de navigation
    slideFrames.forEach((_, i) => {
      const btn = document.createElement("span");
      btn.classList.add("nav-btn");
      if (i === 0) btn.classList.add("active");
      btn.onclick = () => showSlide(i);
      navigation.appendChild(btn);
    });

    // Afficher la première description par défaut
    window.onload = () => showSlide(0);
  </script>
</body>
</html>
