<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Link to your main CSS file -->
    <style>
        /* Background for the entire page */
        body {
            font-family: Arial, sans-serif;
            background-color: #737386; /* Mauve-grey background */
        }

        /* Inline form styling retained */
        .form-container {
            width: 90%;
            max-width: 400px;
            margin: 3rem auto;
            background: #515166;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        .form-container h2 {
            color: #FFFFFF;
            font-size: 1.8rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        .form-container label {
            color: #FFFFFF;
            font-size: 1rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        .form-container input[type="text"],
        .form-container input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            border: 1px solid #737386;
            border-radius: 5px;
            background-color: #737386;
            color: #FFFFFF;
        }
        .form-container input[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            background-color: #4CAF50;
            color: #FFFFFF;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 1rem;
        }
        .form-container input[type="submit"]:hover {
            background-color: #45a049;
        }
        .form-container .create-account-btn {
            width: 100%;
            padding: 0.75rem;
            background-color: #B5A5D6;
            color: #FFFFFF;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
            margin-top: 1rem;
            transition: background-color 0.3s;
        }
        .form-container .create-account-btn:hover {
            background-color: #A294C7;
        }

        /* Header Styling */
        header {
            width: 100%;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: center;
            background-color: #000000; /* Black background */
        }

        .navbar {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        /* Enhanced Navigation Links */
        .nav-links {
            list-style: none;
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 1rem;
            position: relative;
            transition: color 0.3s, transform 0.3s, box-shadow 0.3s;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -3px;
            left: 0;
            background-color: #B5A5D6;
            transition: width 0.3s;
        }

        .nav-links a:hover {
            color: #B5A5D6;
            transform: scale(1.1); /* Slight scale on hover */
            box-shadow: 0px 4px 10px rgba(181, 165, 214, 0.5);
        }

        .nav-links a:hover::after {
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <img src="path-to-your-logo.png" alt="Logo Oncoanalyse" class="logo-img">
            </div>
            <nav class="navbar">
            <ul class="nav-links">
                <li><a href="home.html">Accueil</a></li>
                <li><a href="exploration.html">Exploration</a></li>
                <li><a href="statistique.html">Statistique</a></li>
                <li><a href="visualisation.html">Visualisation</a></li>
                <li><a href="prediction.html">Prédiction</a></li>
                <li><a href="compte.html">Compte</a></li>
            </ul>
        </nav>
    </header>

    <div class="form-container">
        <h2>Connexion</h2>
        <form action="process_login.php" method="POST">
            <label for="adresse">Adresse e-mail</label>
            <input type="text" name="adresse" id="adresse" placeholder="Votre adresse e-mail" required>
            
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" placeholder="Votre mot de passe" required>
            
            <input type="submit" name="login" value="Se connecter">
        </form>

        <a href="signup.php" class="create-account-btn">Créer un compte</a>
    </div>
</body>
</html>
