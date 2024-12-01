<?php
$host = 'localhost';  // Database host
$dbname = 'user_db';  // Database name
$username = 'root';   // Database username (adjust as needed)
$password = '';       // Database password (adjust as needed)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
?>
