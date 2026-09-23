<?php
require 'config.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit; }

$usuario_id = $_SESSION['usuario_id'];
$is_admin   = $_SESSION['is_admin'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remover_id'])) {
    $id_relacao = (int) $_POST['remover_id'];

    $stmt = $conn->prepare("DELETE FROM playlist WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id_relacao, $usuario_id);
    $stmt->execute();
}

$stmt = $conn->prepare("
    SELECT p.id as id_relacao, m.titulo, m.artista, m.url_audio
    FROM playlist p
    JOIN musicas m ON p.musica_id = m.id
    WHERE p.usuario_id = ?
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$minha_playlist = $stmt->get_result();
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
    <header class="topbar">
        <h1 class="logo">playlist<span>.</span></h1>
        <nav>
            <?php if ($is_admin == 1): ?>
                <span class="badge-admin">Admin</span>
                <a href="admin_usuarios.php">Gerir Usuários</a>
            <?php endif; ?>
            <a href="musicas.php">Catálogo</a>
            <a href="logout.php">Sair</a>
        </nav>
    </header>

    <main class="container">
        <h2 class="titulo-secao">Minha playlist</h2>

        <?php if ($minha_playlist->num_rows == 0): ?>
            <p class="vazio">Sua playlist está vazia. Volte ao catálogo e adicione algumas músicas.</p>
        <?php else: ?>
            <div class="grid-musicas">
                <?php while ($m = $minha_playlist->fetch_assoc()):
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

                        <form method="POST">
                            <input type="hidden" name="remover_id" value="<?= $m['id_relacao'] ?>">
                            <button type="submit" class="btn-remover">Remover da playlist</button>
                        </form>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>