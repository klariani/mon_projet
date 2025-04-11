<?php
function getBD(){ 
    try {
        $bdd = new PDO('mysql:host=localhost;dbname=cancer;charset=utf8', 'root', '');
        $bdd = new PDO('mysql:host=localhost;dbname=cancer;charset=utf8', 'root', '');
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    } 
    return $bdd; 
} 
?>