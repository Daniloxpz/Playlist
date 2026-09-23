<?php
require 'config.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit; }

$usuario_id = $_SESSION['usuario_id'];
$is_admin   = $_SESSION['is_admin'] ?? 0;
$mensagem = "";
$erro = false;

// 1. Cadastrar nova música (com verificação de URL duplicada)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastrar_musica'])) {
    $titulo = trim($_POST['titulo']);
    $artista = trim($_POST['artista']);
    $url_audio = trim($_POST['url_audio']);

    // Verifica se a URL já existe no catálogo
    $check_url = $conn->prepare("SELECT id FROM musicas WHERE url_audio = ?");
    $check_url->bind_param("s", $url_audio);
    $check_url->execute();
    
    if ($check_url->get_result()->num_rows > 0) {
        $mensagem = "Erro: Esta música (URL) já está cadastrada no catálogo!";
        $erro = true;
    } else {
        $stmt = $conn->prepare("INSERT INTO musicas (titulo, artista, url_audio) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $titulo, $artista, $url_audio);
        $stmt->execute();
        $mensagem = "Nova música adicionada ao catálogo!";
        $erro = false;
    }
}

// 2. Excluir música do catálogo geral — SOMENTE ADMIN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['excluir_musica_id'])) {
    if ($is_admin == 1) {
        $excluir_id = (int) $_POST['excluir_musica_id'];

        $stmt = $conn->prepare("DELETE FROM playlist WHERE musica_id = ?");
        $stmt->bind_param("i", $excluir_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM musicas WHERE id = ?");
        $stmt->bind_param("i", $excluir_id);
        $stmt->execute();

        $mensagem = "Música excluída do catálogo global!";
    } else {
        $mensagem = "Sem permissão para excluir.";
        $erro = true;
    }
}

// 3. Adicionar MÚLTIPLAS músicas à playlist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adicionar_multiplas'])) {
    if (!empty($_POST['musicas_ids'])) {
        $adicionadas = 0;
        $ja_existiam = 0;

        foreach ($_POST['musicas_ids'] as $musica_id) {
            $musica_id = (int) $musica_id;

            // Verifica duplicados
            $check = $conn->prepare("SELECT id FROM playlist WHERE usuario_id = ? AND musica_id = ?");
            $check->bind_param("ii", $usuario_id, $musica_id);
            $check->execute();
            $existe = $check->get_result();

            if ($existe->num_rows > 0) {
                $ja_existiam++;
            } else {
                $stmt = $conn->prepare("INSERT INTO playlist (usuario_id, musica_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $usuario_id, $musica_id);
                $stmt->execute();
                $adicionadas++;
            }
        }

        if ($adicionadas > 0) {
            $mensagem = "$adicionadas música(s) adicionada(s)! " . ($ja_existiam > 0 ? "($ja_existiam já estavam na lista)." : "");
            $erro = false;
        } else {
            $mensagem = "Todas as músicas selecionadas já estavam na sua playlist.";
            $erro = true;
        }
    } else {
        $mensagem = "Selecione pelo menos uma música para adicionar.";
        $erro = true;
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
                <a href="admin_usuarios.php">Gerir Usuários</a>
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
            <h2>Cadastrar música no catálogo</h2>
            <form method="POST" class="form-linha">
                <input type="hidden" name="cadastrar_musica" value="1">
                <input type="text" name="titulo" placeholder="Título" required>
                <input type="text" name="artista" placeholder="Artista" required>
                <input type="url" name="url_audio" placeholder="Link do YouTube" required>
                <button type="submit">Salvar</button>
            </form>
        </section>

        <!-- Formulário único para gerir as adições em lote -->
        <form method="POST">
            <div style="display: flex; justify-content: space-between; align-items: center; margin: 36px 0 16px;">
                <h2 class="titulo-secao" style="margin: 0; border: none;">Catálogo</h2>
                <button type="submit" name="adicionar_multiplas" class="btn-primario" style="padding: 10px 18px;">➕ Adicionar Selecionadas</button>
            </div>

            <div class="grid-musicas">
                <?php while ($m = $musicas->fetch_assoc()):
                    $link = htmlspecialchars($m['url_audio']);
                    $link = str_replace("watch?v=", "embed/", $link);
                    $link = str_replace("youtu.be/", "youtube.com/embed/", $link);
                ?>
                    <article class="musica-item">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div class="musica-info">
                                <strong><?= htmlspecialchars($m['titulo']) ?></strong>
                                <span><?= htmlspecialchars($m['artista']) ?></span>
                            </div>
                            
                            <!-- Checkbox para selecionar a música -->
                            <label class="checkbox-linha" style="margin: 0; padding: 0;">
                                <input type="checkbox" name="musicas_ids[]" value="<?= $m['id'] ?>">
                            </label>
                        </div>
                        
                        <iframe width="100%" height="200" src="<?= $link ?>" frameborder="0" allow="autoplay; encrypted-media"></iframe>

                        <!-- Botão Admin fica dentro do mesmo form, mas envia outro name/value -->
                        <?php if ($is_admin == 1): ?>
                            <div class="acoes" style="margin-top: auto;">
                                <button type="submit" name="excluir_musica_id" value="<?= $m['id'] ?>" class="btn-remover" onclick="return confirm('Excluir esta música do catálogo para todos os utilizadores?');">Excluir do catálogo</button>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>
            </div>
        </form>
    </main>
</body>
</html>