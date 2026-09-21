<?php
require 'config.php';
if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit; }
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remover_id'])) {
    $id_relacao = $_POST['remover_id'];
    $conn->query("DELETE FROM playlist WHERE id = $id_relacao AND usuario_id = $usuario_id");
}

$query = "SELECT p.id as id_relacao, m.titulo, m.artista, m.url_audio 
          FROM playlist p 
          JOIN musicas m ON p.musica_id = m.id 
          WHERE p.usuario_id = $usuario_id";
$minha_playlist = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Minha Playlist</title>
</head>
<body>
    <h2>Minha Playlist</h2>
    <div><a href="musicas.php">Voltar ao Catálogo</a> | <a href="logout.php">Sair</a></div>
    <hr>
    
    <?php if ($minha_playlist->num_rows == 0): ?>
        <p>Sua playlist está vazia.</p>
    <?php else: ?>
        <?php while ($m = $minha_playlist->fetch_assoc()): 
            // Converte o link normal do YouTube para o formato Embed
            $link = htmlspecialchars($m['url_audio']);
            $link = str_replace("watch?v=", "embed/", $link);
            $link = str_replace("youtu.be/", "youtube.com/embed/", $link);
        ?>
            <div class="musica-item">
                <div class="musica-info">
                    <strong><?= htmlspecialchars($m['titulo']) ?></strong> - <?= htmlspecialchars($m['artista']) ?>
                </div>
                <!-- Player do YouTube -->
                <iframe width="100%" height="200" src="<?= $link ?>" frameborder="0" allow="autoplay; encrypted-media" style="border-radius: 8px;"></iframe>
                
                <form method="POST" style="max-width: 100%; margin-top: 10px;">
                    <input type="hidden" name="remover_id" value="<?= $m['id_relacao'] ?>">
                    <button type="submit" class="btn-remover" style="width: 100%;">Remover da Playlist</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</body>
</html>