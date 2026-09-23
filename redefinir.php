<?php
require 'config.php';

$mensagem = "";
$erro = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $nova_senha = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);

    // Verifica se o email existe no banco
    $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE email = ?");
        $stmt->bind_param("ss", $nova_senha, $email);
        $stmt->execute();
        $mensagem = "Senha atualizada! Você já pode fazer login.";
    } else {
        $mensagem = "E-mail não encontrado no sistema.";
        $erro = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Redefinir Senha - Playlist</title>
</head>
<body>
    <div class="card-login">
        <h1 class="logo">playlist<span>.</span></h1>
        <p class="subtitle">Redefinição rápida de senha</p>

        <?php if ($mensagem): ?>
            <p class="aviso <?= $erro ? 'aviso-erro' : 'aviso-ok' ?>"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Digite seu e-mail cadastrado" required>
            <input type="password" name="nova_senha" placeholder="Digite a nova senha" required>
            <button type="submit">Atualizar Senha</button>
        </form>
        
        <br>
        <a href="index.php" class="link-form">Voltar para o Login</a>
    </div>
</body>
</html>