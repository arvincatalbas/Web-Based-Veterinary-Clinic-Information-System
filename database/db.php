<?php

$host = 'localhost';
$dbname = 'vet_management_system';
$username = 'root';
$password = '';


try {
    $conn = new mysqli("localhost", "root", "", "vet_management_system");
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>