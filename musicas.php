<?php
require 'config.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_musica'])) {
    $titulo = $_POST['titulo'];
    $artista = $_POST['artista'];
    $url_audio = $_POST['url_audio'];
    $stmt = $conn->prepare("INSERT INTO musicas (titulo, artista, url_audio) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $titulo, $artista, $url_audio);
    $stmt->execute();
    $mensagem = "Nova música adicionada!";
}

// LÓGICA DE EXCLUSÃO (SOMENTE ADMIN - ID 1)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['excluir_musica_id']) && $_SESSION['usuario_id'] == 1) {
    $excluir_id = $_POST['excluir_musica_id'];
    $conn->query("DELETE FROM playlist WHERE musica_id = $excluir_id");
    $conn->query("DELETE FROM musicas WHERE id = $excluir_id");
    $mensagem = "Música excluída do sistema!";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['musica_id'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $musica_id = $_POST['musica_id'];
    $conn->query("INSERT INTO playlist (usuario_id, musica_id) VALUES ($usuario_id, $musica_id)");
    $mensagem = "Música adicionada à playlist!";
}

$musicas = $conn->query("SELECT * FROM musicas ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Catálogo - YouTube</title>
</head>
<body>
    <h2>Catálogo de Músicas</h2>
    <div><a href="playlist.php">Ver Minha Playlist</a> | <a href="logout.php">Sair</a></div>
    <hr>
    <?php if(isset($mensagem)) echo "<p style='color:#1db954; margin-bottom:20px;'><b>$mensagem</b></p>"; ?>

    <div class="musica-item" style="border: 1px solid #1db954;">
        <h3 style="color: #fff; margin-bottom: 10px;">Cadastrar Música do YouTube</h3>
        <form method="POST" style="max-width: 100%;">
            <input type="hidden" name="cadastrar_musica" value="1">
            <input type="text" name="titulo" placeholder="Título" required>
            <input type="text" name="artista" placeholder="Artista" required>
            <input type="url" name="url_audio" placeholder="Link do YouTube (ex: youtube.com/watch?v=...)" required>
            <button type="submit" style="width: 100%;">Salvar</button>
        </form>
    </div>
    <hr>
    <?php while ($m = $musicas->fetch_assoc()): 
        $link = htmlspecialchars($m['url_audio']);
        $link = str_replace("watch?v=", "embed/", $link);
        $link = str_replace("youtu.be/", "youtube.com/embed/", $link);
    ?>
        <div class="musica-item">
            <div class="musica-info">
                <strong><?= htmlspecialchars($m['titulo']) ?></strong> - <?= htmlspecialchars($m['artista']) ?>
            </div>
            <iframe width="100%" height="200" src="<?= $link ?>" frameborder="0" allow="autoplay; encrypted-media" style="border-radius: 8px;"></iframe>
            
            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <form method="POST" style="flex: 1;">
                    <input type="hidden" name="musica_id" value="<?= $m['id'] ?>">
                    <button type="submit" style="width: 100%;">Adicionar à Playlist</button>
                </form>
                
                <!-- BOTÃO DE EXCLUSÃO (APENAS PARA O ADMIN) -->
                <?php if ($_SESSION['usuario_id'] == 1): ?>
                <form method="POST" style="flex: 1;">
                    <input type="hidden" name="excluir_musica_id" value="<?= $m['id'] ?>">
                    <button type="submit" class="btn-remover" style="width: 100%;">Excluir</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</body>
</html>