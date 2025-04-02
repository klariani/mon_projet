<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['adresse'];
    $password = $_POST['mot_de_passe'];

    // Mock authentication check - replace with actual database check
    if ($email == "user@example.com" && $password == "password123") {
        $_SESSION["loggedin"] = true;
        header("Location: home.php"); // Redirect to home on successful login
        exit();
    } else {
        echo "Invalid login credentials. <a href='login.php'>Try again</a>";
    }
}
?>
