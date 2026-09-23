<?php
require 'config.php';

// Proteção: Apenas administradores logados podem acessar esta página
if (!isset($_SESSION['usuario_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: musicas.php");
    exit;
}

$mensagem = "";
$erro = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['excluir_usuario_id'])) {
    $excluir_id = (int) $_POST['excluir_usuario_id'];

    if ($excluir_id === $_SESSION['usuario_id']) {
        $mensagem = "Ação negada: Você não pode excluir sua própria conta.";
        $erro = true;
    } else {
        $stmt = $conn->prepare("DELETE FROM playlist WHERE usuario_id = ?");
        $stmt->bind_param("i", $excluir_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $excluir_id);
        
        if ($stmt->execute()) {
            $mensagem = "Usuário excluído com sucesso!";
        } else {
            $mensagem = "Erro ao excluir o usuário.";
            $erro = true;
        }
    }
}

$usuarios = $conn->query("SELECT id, nome, email, is_admin FROM usuarios ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Gerenciar Usuários - Playlist</title>
</head>
<body>
    <header class="topbar">
        <h1 class="logo">playlist<span>.</span></h1>
        <nav>
            <span class="badge-admin">Admin</span>
            <a href="musicas.php">Catálogo</a>
            <a href="playlist.php">Minha playlist</a>
            <a href="logout.php">Sair</a>
        </nav>
    </header>

    <main class="container">
        <h2 class="titulo-secao">Gerenciar Usuários</h2>

        <?php if ($mensagem): ?>
            <p class="aviso <?= $erro ? 'aviso-erro' : 'aviso-ok' ?>"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <div class="lista-usuarios">
            <?php while ($u = $usuarios->fetch_assoc()): ?>
                <div class="usuario-item">
                    <div class="usuario-info">
                        <strong><?= htmlspecialchars($u['nome']) ?> <?= $u['is_admin'] ? '<span class="badge-admin" style="display:inline-block; margin-left:8px;">Admin</span>' : '' ?></strong>
                        <span><?= htmlspecialchars($u['email']) ?></span>
                    </div>

                    <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                        <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir o usuário <?= htmlspecialchars($u['nome']) ?> e toda a sua playlist?');">
                            <input type="hidden" name="excluir_usuario_id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn-remover">Excluir Usuário</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </main>
</body>
</html>