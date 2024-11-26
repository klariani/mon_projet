<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    
    <style>
        /* Body Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #737386; /* Mauve-grey background */
            margin: 0;
            padding: 0;
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
            margin: 0;
            padding: 0;
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

        /* Form specific styling */
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
            color: #FFFFFF; /* Texte en blanc */
            font-size: 1rem;
            margin-bottom: 0.5rem;
            display: block; /* Place les labels au-dessus des champs */
        }

        .form-container input[type="text"],
        .form-container input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
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
        }

        .form-container input[type="submit"]:hover {
            background-color: #45a049;
        }

        .error {
            color: #ff4d4d;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
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
        <h2>Inscription</h2>
        <form action="process.php" method="POST">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" placeholder="Nom" required>

            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" placeholder="Prénom" required>

            <label for="adresse">Adresse</label>
            <input type="text" id="adresse" name="adresse" placeholder="Adresse" required>

            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Mot de passe" required>

            <label for="confirm_mot_de_passe">Confirmez le mot de passe</label>
            <input type="password" id="confirm_mot_de_passe" name="confirm_mot_de_passe" placeholder="Confirmez le mot de passe" required>

            <input type="submit" name="signup" value="S'inscrire">
        </form>
    </div>
</body>
</html>
