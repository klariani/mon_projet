<?php
function getBD(){
$bdd = new PDO('mysql:host=localhost;dbname=oncology;charset=utf8','root','root');
return $bdd;
}
?>