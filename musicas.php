<?php
require 'config.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit; }

$usuario_id = $_SESSION['usuario_id'];
$is_admin   = $_SESSION['is_admin'] ?? 0;
$mensagem = "";
$erro = false;

// Cadastrar nova música (qualquer usuário logado pode cadastrar no catálogo)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_musica'])) {
    $titulo = trim($_POST['titulo']);
    $artista = trim($_POST['artista']);
    $url_audio = trim($_POST['url_audio']);

    $stmt = $conn->prepare("INSERT INTO musicas (titulo, artista, url_audio) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $titulo, $artista, $url_audio);
    $stmt->execute();
    $mensagem = "Nova música adicionada ao catálogo!";
}

// Excluir música do catálogo geral — SOMENTE ADMIN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['excluir_musica_id'])) {
    if ($is_admin == 1) {
        $excluir_id = (int) $_POST['excluir_musica_id'];

        $stmt = $conn->prepare("DELETE FROM playlist WHERE musica_id = ?");
        $stmt->bind_param("i", $excluir_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM musicas WHERE id = ?");
        $stmt->bind_param("i", $excluir_id);
        $stmt->execute();

        $mensagem = "Música excluída do catálogo!";
    } else {
        $mensagem = "Você não tem permissão para excluir músicas do catálogo.";
        $erro = true;
    }
}

// Adicionar música à playlist do usuário (com checagem de duplicado)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['musica_id'])) {
    $musica_id = (int) $_POST['musica_id'];

    $check = $conn->prepare("SELECT id FROM playlist WHERE usuario_id = ? AND musica_id = ?");
    $check->bind_param("ii", $usuario_id, $musica_id);
    $check->execute();
    $existe = $check->get_result();

    if ($existe->num_rows > 0) {
        $mensagem = "Essa música já está na sua playlist!";
        $erro = true;
    } else {
        $stmt = $conn->prepare("INSERT INTO playlist (usuario_id, musica_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $usuario_id, $musica_id);
        $stmt->execute();
        $mensagem = "Música adicionada à sua playlist!";
    }
}

$musicas = $conn->query("SELECT * FROM musicas ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Catálogo - Playlist</title>
</head>
<body>
    <header class="topbar">
        <h1 class="logo">playlist<span>.</span></h1>
        <nav>
            <?php if ($is_admin == 1): ?>
                <span class="badge-admin">Admin</span>
            <?php endif; ?>
            <a href="playlist.php">Minha playlist</a>
            <a href="logout.php">Sair</a>
        </nav>
    </header>

    <main class="container">
        <?php if ($mensagem): ?>
            <p class="aviso <?= $erro ? 'aviso-erro' : 'aviso-ok' ?>"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <section class="painel-cadastro">
            <h2>Cadastrar música</h2>
            <form method="POST" class="form-linha">
                <input type="hidden" name="cadastrar_musica" value="1">
                <input type="text" name="titulo" placeholder="Título" required>
                <input type="text" name="artista" placeholder="Artista" required>
                <input type="url" name="url_audio" placeholder="Link do YouTube" required>
                <button type="submit">Salvar</button>
            </form>
        </section>

        <h2 class="titulo-secao">Catálogo</h2>
        <div class="grid-musicas">
            <?php while ($m = $musicas->fetch_assoc()):
                $link = htmlspecialchars($m['url_audio']);
                $link = str_replace("watch?v=", "embed/", $link);
                $link = str_replace("youtu.be/", "youtube.com/embed/", $link);
            ?>
                <article class="musica-item">
                    <div class="musica-info">
                        <strong><?= htmlspecialchars($m['titulo']) ?></strong>
                        <span><?= htmlspecialchars($m['artista']) ?></span>
                    </div>
                    <iframe width="100%" height="200" src="<?= $link ?>" frameborder="0" allow="autoplay; encrypted-media"></iframe>

                    <div class="acoes">
                        <form method="POST">
                            <input type="hidden" name="musica_id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn-primario">Adicionar à playlist</button>
                        </form>

                        <?php if ($is_admin == 1): ?>
                        <form method="POST" onsubmit="return confirm('Excluir esta música do catálogo para todos os usuários?');">
                            <input type="hidden" name="excluir_musica_id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn-remover">Excluir do catálogo</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    </main>
</body>
</html>