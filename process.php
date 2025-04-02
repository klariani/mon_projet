<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['signup'])) {
        // Signup form processing
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $adresse = trim($_POST['adresse']);
        $mot_de_passe = $_POST['mot_de_passe'];
        $confirm_mot_de_passe = $_POST['confirm_mot_de_passe'];

        // Check for required fields
        if (empty($nom) || empty($prenom) || empty($adresse) || empty($mot_de_passe)) {
            echo "<p class='error'>Tous les champs sont obligatoires.</p>";
            exit;
        }

        // Check if passwords match
        if ($mot_de_passe !== $confirm_mot_de_passe) {
            echo "<p class='error'>Les mots de passe ne correspondent pas.</p>";
            exit;
        }

        // Here, you would typically insert the user data into a database
        // Simulate success
        echo "<p>Inscription réussie! Vous pouvez maintenant vous <a href='login.php'>connecter</a>.</p>";
        
    } elseif (isset($_POST['login'])) {
        // Login form processing
        $adresse = trim($_POST['adresse']);
        $mot_de_passe = $_POST['mot_de_passe'];

        // Check for required fields
        if (empty($adresse) || empty($mot_de_passe)) {
            echo "<p class='error'>Adresse et mot de passe sont obligatoires.</p>";
            exit;
        }

        // Mock authentication check - replace with actual database check
        if ($adresse === "user@example.com" && $mot_de_passe === "password123") {
            $_SESSION["loggedin"] = true;
            $_SESSION["adresse"] = $adresse;

            echo "<p>Connexion réussie! Bienvenue $adresse.</p>";
            header("Location: home.php"); // Redirect to home page after login
            exit();
        } else {
            echo "<p class='error'>Identifiants incorrects. <a href='login.php'>Réessayez</a>.</p>";
            exit();
        }
    }
}
?>
