<?php
require 'config.php';

$erro_login = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $quer_admin = isset($_POST['entrar_admin']);
    $senha_admin = $_POST['senha_admin'] ?? '';

    $stmt = $conn->prepare("SELECT id, is_admin FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $usuario_id = $row['id'];
        $is_admin = (int) $row['is_admin'];
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email) VALUES (?, ?)");
        $stmt->bind_param("ss", $nome, $email);
        $stmt->execute();
        $usuario_id = $conn->insert_id;
        $is_admin = 0;
    }

    if ($quer_admin && $is_admin == 0) {
        if ($senha_admin === ADMIN_PASSWORD) {
            $is_admin = 1;
            $upd = $conn->prepare("UPDATE usuarios SET is_admin = 1 WHERE id = ?");
            $upd->bind_param("i", $usuario_id);
            $upd->execute();
        } else {
            $erro_login = "Senha de administrador incorreta.";
        }
    }

    if ($erro_login === "") {
        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['is_admin']   = $is_admin;
        header("Location: musicas.php");
        exit;
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
        <p class="subtitle">Entre com seu nome e e-mail para acessar o catálogo</p>

        <?php if ($erro_login): ?>
            <p class="aviso aviso-erro"><?= htmlspecialchars($erro_login) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="nome" placeholder="Seu nome" required>
            <input type="email" name="email" placeholder="Seu e-mail" required>

            <label class="checkbox-linha">
                <input type="checkbox" name="entrar_admin" id="entrar_admin">
                <span>Entrar como administrador</span>
            </label>

            <input type="password" name="senha_admin" id="senha_admin" placeholder="Senha de administrador" class="campo-oculto">

            <button type="submit">Acessar</button>
        </form>
    </div>

    <script>
        const checkbox = document.getElementById('entrar_admin');
        const campoSenha = document.getElementById('senha_admin');
        checkbox.addEventListener('change', () => {
            campoSenha.classList.toggle('campo-oculto', !checkbox.checked);
            if (checkbox.checked) campoSenha.focus();
        });
    </script>
</body>
</html>