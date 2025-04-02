<?php
function getBD(){ 
    try {
<<<<<<< HEAD
        $bdd = new PDO('mysql:host=localhost;dbname=cancer;charset=utf8', 'root', '');
=======
        $bdd = new PDO('mysql:host=localhost;dbname=cancer;charset=utf8', 'root', 'root');
>>>>>>> a1dc4dba1a2451c38c6a10c5a7b0438a8a1b82ab
    } catch (Exception $e) {
        die('Erreur : ' . $e->getMessage());
    } 
    return $bdd; 
} 
?>