<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Oncoanalyse</title>
    
    <!-- Feuille de style principale -->
    <link rel="stylesheet" href="assets/css/main.css" />
    <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
    
    <!-- FontAwesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css"
          integrity="sha512-..."
          crossorigin="anonymous"
          referrerpolicy="no-referrer" />
    
    <style>
        .signup-container {
            max-width: 500px;
            margin: 4rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .signup-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .signup-header h2 {
            color: #444;
            font-size: 2rem;
            position: relative;
            display: inline-block;
        }
        
        .signup-header h2:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #5D9CEC, #A89CC8);
        }
        
        .signup-form {
            margin-top: 2rem;
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }
        
        .form-group i {
            color: #5D9CEC;
            margin-right: 10px;
            font-size: 1.2rem;
            vertical-align: middle;
        }
        
        .form-input {
            display: block;
            width: 100%;
            padding: 0.8rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #f8f9fa;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 8px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-input:focus {
            border-color: #5D9CEC;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(93, 156, 236, 0.25);
        }
        
        .signup-btn {
            display: block;
            width: 100%;
            padding: 0.8rem 1rem;
            font-size: 1rem;
            font-weight: 600;
            text-align: center;
            color: #fff;
            background: linear-gradient(90deg, #5D9CEC, #A89CC8);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(93, 156, 236, 0.3);
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #777;
            font-size: 0.9rem;
        }
        
        .login-link a {
            color: #5D9CEC;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .login-link a:hover {
            color: #4A89DC;
            text-decoration: underline;
        }
        
        /* Indicateur de force du mot de passe */
        .password-strength {
            height: 5px;
            margin-top: 0.5rem;
            border-radius: 5px;
            background-color: #ecf0f1;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .password-strength-text {
            font-size: 0.8rem;
            margin-top: 0.3rem;
            text-align: right;
            color: #7f8c8d;
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .signup-container {
            animation: fadeIn 0.6s ease-out;
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body class="is-preload">

    <!-- Wrapper -->
    <div id="wrapper">

        <!-- Header -->
        <header id="header" class="alt">
            <h1>Oncoanalyse</h1>
            <p>Prédire la nature des tumeurs du sein</p>
        </header>

        <!-- Nav -->
        <nav id="nav">
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="index.php#exploration">Exploration</a></li>
                <li><a href="index.php#analyse">Statistique</a></li>
                <li><a href="index.php#visualisation">Visualisation</a></li>
                <li><a href="index.php#prediction">Prédiction</a></li>
                <li><a href="login.php" class="active">Compte</a></li>
            </ul>
        </nav>

        <!-- Main -->
        <div id="main">
            <div class="signup-container">
                <div class="signup-header">
                    <h2>Créer un compte</h2>
                </div>
                
                <form class="signup-form" action="process.php" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom"><i class="fas fa-user"></i>Nom</label>
                            <input type="text" id="nom" name="nom" class="form-input" placeholder="Votre nom" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom"><i class="fas fa-user"></i>Prénom</label>
                            <input type="text" id="prenom" name="prenom" class="form-input" placeholder="Votre prénom" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="adresse"><i class="fas fa-envelope"></i>Adresse e-mail</label>
                        <input type="email" id="adresse" name="adresse" class="form-input" placeholder="Votre adresse e-mail" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mot_de_passe"><i class="fas fa-lock"></i>Mot de passe</label>
                        <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-input" placeholder="Choisissez un mot de passe" required>
                        <div class="password-strength">
                            <div class="password-strength-bar"></div>
                        </div>
                        <div class="password-strength-text">Force: <span id="strength-text">Faible</span></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_mot_de_passe"><i class="fas fa-lock"></i>Confirmer le mot de passe</label>
                        <input type="password" id="confirm_mot_de_passe" name="confirm_mot_de_passe" class="form-input" placeholder="Confirmez votre mot de passe" required>
                    </div>
                    
                    <button type="submit" name="signup" class="signup-btn">S'inscrire</button>
                </form>
                
                <div class="login-link">
                    <p>Vous avez déjà un compte ? <a href="login.php">Connectez-vous</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/jquery.scrollex.min.js"></script>
    <script src="assets/js/jquery.scrolly.min.js"></script>
    <script src="assets/js/browser.min.js"></script>
    <script src="assets/js/breakpoints.min.js"></script>
    <script src="assets/js/util.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation et interactivité pour les champs de formulaire
            const inputs = document.querySelectorAll('.form-input');
            const passwordInput = document.getElementById('mot_de_passe');
            const confirmPasswordInput = document.getElementById('confirm_mot_de_passe');
            const strengthBar = document.querySelector('.password-strength-bar');
            const strengthText = document.getElementById('strength-text');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-5px)';
                    this.parentElement.style.transition = 'transform 0.3s ease';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });
            
            // Afficher/masquer les mots de passe
            const passwordFields = [passwordInput, confirmPasswordInput];
            
            passwordFields.forEach((field, index) => {
                const togglePassword = document.createElement('i');
                togglePassword.className = 'fas fa-eye';
                togglePassword.style.position = 'absolute';
                togglePassword.style.right = '15px';
                togglePassword.style.top = '38px';
                togglePassword.style.cursor = 'pointer';
                togglePassword.style.color = '#5D9CEC';
                
                field.parentElement.style.position = 'relative';
                field.parentElement.appendChild(togglePassword);
                
                togglePassword.addEventListener('click', function() {
                    if (field.type === 'password') {
                        field.type = 'text';
                        togglePassword.className = 'fas fa-eye-slash';
                    } else {
                        field.type = 'password';
                        togglePassword.className = 'fas fa-eye';
                    }
                });
            });
            
            // Vérification de la force du mot de passe
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Critères de force
                if (password.length >= 8) strength += 25;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 25;
                if (password.match(/\d/)) strength += 25;
                if (password.match(/[^a-zA-Z0-9]/)) strength += 25;
                
                // Mise à jour de la barre de force
                strengthBar.style.width = strength + '%';
                
                // Couleur de la barre en fonction de la force
                if (strength <= 25) {
                    strengthBar.style.backgroundColor = '#e74c3c';
                    strengthText.textContent = 'Faible';
                } else if (strength <= 50) {
                    strengthBar.style.backgroundColor = '#f39c12';
                    strengthText.textContent = 'Moyen';
                } else if (strength <= 75) {
                    strengthBar.style.backgroundColor = '#3498db';
                    strengthText.textContent = 'Bon';
                } else {
                    strengthBar.style.backgroundColor = '#2ecc71';
                    strengthText.textContent = 'Fort';
                }
            });
            
            // Vérification de la correspondance des mots de passe
            confirmPasswordInput.addEventListener('input', function() {
                if (this.value === passwordInput.value) {
                    this.style.borderColor = '#2ecc71';
                } else {
                    this.style.borderColor = '#e74c3c';
                }
            });
            
            // Validation du formulaire avant envoi
            document.querySelector('.signup-form').addEventListener('submit', function(e) {
                if (passwordInput.value !== confirmPasswordInput.value) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas.');
                }
            });
        });
    </script>
</body>
</html>