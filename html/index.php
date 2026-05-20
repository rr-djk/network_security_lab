<?php
echo "<h1>Test de la Partie 1 (Version Sécurisée)</h1>";
echo "<p>PHP fonctionne correctement. Version actuelle : " . phpversion() . "</p>";

// Récupération dynamique du mot de passe depuis l'environnement du conteneur
$host = 'database_server'; 
$user = 'root';
$pass = getenv('MYSQL_ROOT_PASSWORD');

try {
    if (!$pass) {
        throw new Exception("Le mot de passe n'a pas pu être récupéré depuis les variables d'environnement.");
    }

    $conn = new PDO("mysql:host=$host", $user, $pass);
    echo "<p style='color:green;'>✔ Connexion réussie à la base de données MySQL !</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>✘ Échec de la connexion : " . $e->getMessage() . "</p>";
}
?>
