<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'playlist_app';

define('ADMIN_PASSWORD', '123456'); // defina sua senha de admin aqui

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro de ligação: " . $conn->connect_error);
}
?>