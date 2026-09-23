<?php
require 'config.php';

$mensagem = "";
$erro = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao == 'login') {
        $email = trim($_POST['email']);
        $senha = $_POST['senha'];
        $quer_admin = isset($_POST['entrar_admin']);
        $senha_admin = $_POST['senha_admin'] ?? '';

        $stmt = $conn->prepare("SELECT id, senha, is_admin FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Verifica a senha individual do usuário
            if (password_verify($senha, $row['senha'])) {
                $usuario_id = $row['id'];
                $is_admin = (int) $row['is_admin'];

                // Verifica elevação para admin
                if ($quer_admin && $is_admin == 0) {
                    if ($senha_admin === ADMIN_PASSWORD) {
                        $is_admin = 1;
                        $upd = $conn->prepare("UPDATE usuarios SET is_admin = 1 WHERE id = ?");
                        $upd->bind_param("i", $usuario_id);
                        $upd->execute();
                    } else {
                        $mensagem = "Senha de administrador incorreta.";
                        $erro = true;
                    }
                }

                if (!$erro) {
                    $_SESSION['usuario_id'] = $usuario_id;
                    $_SESSION['is_admin']   = $is_admin;
                    header("Location: musicas.php");
                    exit;
                }
            } else {
                $mensagem = "Senha incorreta.";
                $erro = true;
            }
        } else {
            $mensagem = "Usuário não encontrado.";
            $erro = true;
        }

    } elseif ($acao == 'cadastrar') {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografa a senha

        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $mensagem = "Este e-mail já está cadastrado.";
            $erro = true;
        } else {
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $senha);
            $stmt->execute();
            $mensagem = "Cadastro realizado com sucesso! Faça login.";
            $erro = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Entrar - Playlist</title>
</head>
<body>
    <div class="card-login">
        <h1 class="logo">playlist<span>.</span></h1>
        
        <?php if ($mensagem): ?>
            <p class="aviso <?= $erro ? 'aviso-erro' : 'aviso-ok' ?>"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <!-- Formulário de Login -->
        <div id="form-login">
            <p class="subtitle">Acesse sua conta para ouvir o catálogo</p>
            <form method="POST">
                <input type="hidden" name="acao" value="login">
                <input type="email" name="email" placeholder="Seu e-mail" required>
                <input type="password" name="senha" placeholder="Sua senha" required>

                <label class="checkbox-linha">
                    <input type="checkbox" name="entrar_admin" id="entrar_admin">
                    <span>Entrar como administrador</span>
                </label>
                <input type="password" name="senha_admin" id="senha_admin" placeholder="Senha de administrador" class="campo-oculto">

                <button type="submit">Acessar</button>
            </form>
            <p class="link-form" onclick="alternarForms()">Não tem conta? Cadastre-se</p>
            <br>
            <a href="redefinir.php" class="link-form" style="margin-top:5px;">Esqueceu a senha?</a>
        </div>

        <!-- Formulário de Cadastro -->
        <div id="form-cadastro" class="campo-oculto">
            <p class="subtitle">Crie sua conta gratuitamente</p>
            <form method="POST">
                <input type="hidden" name="acao" value="cadastrar">
                <input type="text" name="nome" placeholder="Seu nome" required>
                <input type="email" name="email" placeholder="Seu e-mail" required>
                <input type="password" name="senha" placeholder="Crie uma senha" required>
                <button type="submit">Cadastrar</button>
            </form>
            <p class="link-form" onclick="alternarForms()">Já tem conta? Faça login</p>
        </div>
    </div>

    <script>
        // Alternar entre senha de admin
        const checkbox = document.getElementById('entrar_admin');
        const campoSenha = document.getElementById('senha_admin');
        checkbox.addEventListener('change', () => {
            campoSenha.classList.toggle('campo-oculto', !checkbox.checked);
            if (checkbox.checked) campoSenha.focus();
        });

        // Alternar entre Login e Cadastro
        function alternarForms() {
            const login = document.getElementById('form-login');
            const cadastro = document.getElementById('form-cadastro');
            login.classList.toggle('campo-oculto');
            cadastro.classList.toggle('campo-oculto');
        }
    </script>
</body>
</html>