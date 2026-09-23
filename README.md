# Catálogo de Músicas Web 🎵

# Catálogo de Músicas Web 🎵

Um sistema web simples desenvolvido em PHP e MySQL que permite aos utilizadores visualizar um catálogo geral de músicas do YouTube, adicionar faixas às suas playlists pessoais e ouvir os áudios diretamente na plataforma.

## 🛠️ Tecnologias Utilizadas
* **PHP** (Lógica de servidor e gestão de sessões)
* **MySQL** (Base de dados)
* **HTML / CSS** (Estrutura e visual da interface)
* **XAMPP** (Ambiente de desenvolvimento local)

## ⚙️ Como instalar e rodar localmente

### 1. Pré-requisitos
Certifique-se de que tem o **XAMPP** instalado no seu computador.

### 2. Preparar os ficheiros
1. Faça o clone deste repositório ou baixe o ficheiro ZIP.
2. Extraia e mova a pasta do projeto para dentro do diretório `htdocs` do seu XAMPP.
   * *Exemplo de caminho no Windows:* `C:\xampp\htdocs\playlist-web`

### 3. Configurar a Base de Dados
1. Abra o painel de controlo do XAMPP e inicie os módulos **Apache** e **MySQL**.
2. No navegador, aceda a: `http://localhost/phpmyadmin`
3. Clique no separador **SQL** localizado no menu superior.
4. Abra o ficheiro `banco.sql` (disponível na pasta do projeto), copie todo o código e cole na caixa de texto do phpMyAdmin.
5. Clique em **Executar** para criar a base de dados `playlist_app` e as respetivas tabelas automaticamente.

### 4. Aceder ao Sistema
1. Abra um novo separador no navegador.
2. Digite o endereço correspondente ao nome da pasta do projeto, por exemplo:
   `http://localhost/playlist-web`
3. Crie uma nova conta no ecrã de início e comece a utilizar!

## 🔑 Níveis de Acesso
* **Utilizador Padrão:** Pode criar uma conta, ver o catálogo geral, ouvir as músicas e adicioná-las/removê-las da sua playlist pessoal.
* **Administrador:** Para entrar como administrador, marque a opção **"Entrar como administrador"** no ecrã de login e introduza a senha de administração (definida previamente no ficheiro `config.php`). Privilégios do admin:
  * **Gerir Catálogo:** Pode excluir músicas permanentemente do catálogo geral (removendo-as automaticamente de todos os utilizadores).
  * **Gerir Utilizadores:** Tem acesso a um painel exclusivo para visualizar a lista de utilizadores e excluir contas (ao excluir um utilizador, a sua playlist é apagada automaticamente do sistema).