<?php
	function getBD(){ 
	try {
    $bdd = new PDO('mysql:host=localhost;dbname=haouasnia_imobilier;charset=utf8', 'root', 'root');
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
  <title> Visuaslisation </title>
 </head>
 <body>
  <div class="navigation">
   <ul>
    <li><a href="exploration.html">Exploration</a></li>
    <li><a href="analyseChoix.php">Statistique</a></li>
    <li><a href="visualisation.html">Visualisation</a></li>
    <li><a href="prediction.html">Prédiction</a></li>
    <li><a href="compte.html">Compte</a></li>
   </ul>
  </div>
  <img src="../Capture d'écran 2024-10-26 081223.png">
 <div id="contenu">
	<div class="menuData">
		<label for="data">Choisir data :</label>
			<select id="data" name="data">
				<option value="moyenne">M</option>
				<option value="se">SD</option>
				<option value="wrost">W</option>
			</select>
	</div>
	<ul class="data-list">
  <?php
  $requete = $bdd->prepare('SELECT tumeur.`Id-tumeur`, diagnostique.libelle_diagnostic FROM tumeur JOIN diagnostic ON tumeur.`Id-tumeur` = diagnostic.`Id-tumeur` JOIN diagnostique ON diagnostique.code_diagnostic = diagnostic.code_diagnostic';
    $id = $requete->fetch();
      <li>521 M</li>
      <li>245 B</li>
      <li>7 B</li>
      <li>439 M</li>
      <li>122 B</li>
      <li>540 M</li>
      <li>521 M</li>
      <li>245 B</li>
	</ul>
  ?>
  <table>
  <tr>
    <td>Rayon</td>
	<td>Périmètre</td>
	<td>lissage</td>
	<td>Concavité</td>
	<td>Symétrie</td>
  </tr>
  <tr>
    <td>21.71</td>
	<td>140,9</td>
	<td>0.0934</td>
	<td>0.1168</td>
	<td>0.1717</td>
  </tr>
	</table>
</div>
 </body>