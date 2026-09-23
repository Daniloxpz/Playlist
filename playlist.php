<?php
require 'config.php';

if (!isset($_SESSION['usuario_id'])) { header("Location: index.php"); exit; }

$usuario_id = $_SESSION['usuario_id'];
$is_admin   = $_SESSION['is_admin'] ?? 0;
$mensagem = "";
$erro = false;

// Lógica de UPDATE (Atualizar Nome do Usuário)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['novo_nome'])) {
    $novo_nome = trim($_POST['novo_nome']);
    
    $stmt = $conn->prepare("UPDATE usuarios SET nome = ? WHERE id = ?");
    $stmt->bind_param("si", $novo_nome, $usuario_id);
    
    if ($stmt->execute()) {
        $mensagem = "Seu nome foi atualizado com sucesso!";
        $erro = false;
    } else {
        $mensagem = "Erro ao atualizar nome.";
        $erro = true;
    }
}

// Remover música da playlist
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remover_id'])) {
    $id_relacao = (int) $_POST['remover_id'];

    $stmt = $conn->prepare("DELETE FROM playlist WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id_relacao, $usuario_id);
    $stmt->execute();
}

// Busca o nome atual do usuário no banco
$stmt_user = $conn->prepare("SELECT nome FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $usuario_id);
$stmt_user->execute();
$nome_atual = $stmt_user->get_result()->fetch_assoc()['nome'];

// Busca as músicas da playlist
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
        <?php if ($mensagem): ?>
            <p class="aviso <?= $erro ? 'aviso-erro' : 'aviso-ok' ?>"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <!-- Formulário de Editar Perfil (UPDATE) -->
        <section class="painel-cadastro" style="margin-bottom: 30px;">
            <h2>Meu Perfil</h2>
            <form method="POST" class="form-linha">
                <input type="text" name="novo_nome" value="<?= htmlspecialchars($nome_atual) ?>" required placeholder="Seu nome">
                <button type="submit">Atualizar Nome</button>
            </form>
        </section>

        <h2 class="titulo-secao">Minha playlist</h2>

        <?php if ($minha_playlist->num_rows == 0): ?>
            <p class="vazio">Sua playlist está vazia. Volte ao catálogo e adicione algumas músicas.</p>
        <?php else: ?>
            <div class="grid-musicas">
                <?php while ($m = $minha_playlist->fetch_assoc()):
                    $link = htmlspecialchars($m['url_audio']);
                    // Converte o link para o formato embed
                    $link = str_replace("watch?v=", "embed/", $link);
                    $link = str_replace("youtu.be/", "youtube.com/embed/", $link);
                    
                    // Garante que não há outros parâmetros bagunçando e adiciona o JS API
                    $link = explode('&', $link)[0]; 
                    if (strpos($link, '?') === false) {
                        $link .= "?enablejsapi=1";
                    } else {
                        $link .= "&enablejsapi=1";
                    }
                ?>
                    <article class="musica-item">
                        <div class="musica-info">
                            <strong><?= htmlspecialchars($m['titulo']) ?></strong>
                            <span><?= htmlspecialchars($m['artista']) ?></span>
                        </div>
                        
                        <!-- O iframe agora carrega a API do YouTube -->
                        <iframe class="yt-player" width="100%" height="200" src="<?= $link ?>" frameborder="0" allow="autoplay; encrypted-media"></iframe>

                        <form method="POST">
                            <input type="hidden" name="remover_id" value="<?= $m['id_relacao'] ?>">
                            <button type="submit" class="btn-remover">Remover da playlist</button>
                        </form>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- SCRIPT PARA REPRODUÇÃO AUTOMÁTICA EM SEQUÊNCIA -->
    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
        var players = []; // Lista que vai guardar todos os players

        // Função nativa do YouTube que roda assim que a API carrega
        function onYouTubeIframeAPIReady() {
            var iframes = document.querySelectorAll('.yt-player');
            
            iframes.forEach(function(iframe, index) {
                var player = new YT.Player(iframe, {
                    events: {
                        'onStateChange': function(event) {
                            // YT.PlayerState.ENDED = 0 (Significa que o vídeo acabou)
                            if (event.data === 0) {
                                // Verifica se existe um próximo vídeo na lista
                                if (index + 1 < players.length) {
                                    // Dá o play no próximo vídeo
                                    players[index + 1].playVideo();
                                    
                                    // (Opcional) Rola a tela suavemente até a próxima música
                                    iframes[index + 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                            }
                        }
                    }
                });
                players.push(player); // Salva o player na lista
            });
        }
    </script>
</body>
</html>