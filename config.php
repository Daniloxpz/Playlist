<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = ''; 
$db   = 'playlist_app'; // O nome correto que está no banco.sql

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro de ligação: " . $conn->connect_error);
}
?>