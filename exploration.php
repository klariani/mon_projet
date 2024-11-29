<?php
	function getBD(){ 
	try {
    $bdd = new PDO('mysql:host=localhost;dbname=cancer;charset=utf8', 'root', 'root');
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
} 
	return $bdd; 
	} 
	$bdd = getBD();
	session_start();
?>

<!DOCTYPE html>
<html>
<head>
	<link href="https://fonts.googleapis.com/css2?family=Kaisei+HarunoUmi&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="style1.css" type="text/css" media="screen" />
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title> Exploration-Histogramme </title>
</head>
<body>
	<div class="navigation">
		<ul>
			<li><a href="exploration.html">Exploration</a></li>
			<li><a href="statistique.html">Statistique</a></li>
			<li><a href="visualisation.html">Visualisation</a></li>
			<li><a href="prediction.html">Prédiction</a></li>
			<li><a href="login.php">Compte</a></li>
		</ul>
	</div>
	<img src="./image/logo.png">
	<div id="contenu">
		<div class="menuData">
			<label for="data">Choisir data :</label>
				<select id="data" name="data">
					<option value="rayon moyen">rayon moyen</option>
					<option value="aire moyenne">aire moyenne</option>
					<option value="concavite pire">concavité pire</option>
					<option value="perimetre pire">périmètre pire</option>
					<option value="rayon pire">rayon pire</option>
					<option value="lissage moyen">lissage moyen</option>
					<option value="fractal dim pire">fractal dimension pire</option>
				</select>
		</div>
	</div>
 
 </body>
 </html>