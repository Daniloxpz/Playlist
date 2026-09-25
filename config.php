<?php
session_start();

$host = 'sql309.infinityfree.com';
$user = 'if0_42975526';
$pass = 'Senha1234xp'; // defina sua senha aqui
$db   = 'if0_42975526_playlist';

define('ADMIN_PASSWORD', '123456'); // defina sua senha de admin aqui

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro de ligação: " . $conn->connect_error);
}
?>