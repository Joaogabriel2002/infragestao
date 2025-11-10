<?php
// /admin/usuarios/processar.php (ATUALIZADO COM UPLOAD)

// 1. Inicia a sessão
session_start();

// 2. Define a URL base e o CAMINHO FÍSICO para uploads
$base_url = "/infragestao"; 
$upload_dir = $_SERVER['DOCUMENT_ROOT'] . $base_url . "/uploads/avatars/";

// 3. Inclui APENAS a conexão
require_once $_SERVER['DOCUMENT_ROOT'] . $base_url . '/config/conexao.php';

// 4. Segurança de Admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] != 'ADMIN') {
    header("Location: {$base_url}/login.php");
    exit;
}
$usuario_id_logado = $_SESSION['usuario_id'];

// 5. Pega a 'acao'
$acao = $_POST['acao'] ?? $_GET['acao'] ?? 'nenhuma';

// =======================================================
// ROTEAMENTO DA AÇÃO
// =======================================================

try {
    if ($acao === 'novo_usuario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // ... (A lógica de 'novo_usuario' continua a mesma) ...
        // (Por simplicidade, não vamos adicionar upload na criação, só na edição)
        
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $role = $_POST['role'];
        $setor_id = empty($_POST['setor_id']) ? null : $_POST['setor_id'];
        $unidade_id = empty($_POST['unidade_id']) ? null : $_POST['unidade_id'];
        
        if (empty($nome) || empty($email) || empty($senha) || empty($role)) {
             header("Location: index.php?erro=campos_vazios");
             exit;
        }

        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, email, senha_hash, role, setor_id, unidade_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $senha_hash, $role, $setor_id, $unidade_id]);
        
        header("Location: index.php?sucesso=novo");
        exit;

    } elseif ($acao === 'editar_usuario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // --- (MUDANÇA) LÓGICA DE EDITAR USUÁRIO COM UPLOAD ---
        
        $id_usuario = $_POST['id_usuario'];
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $setor_id = empty($_POST['setor_id']) ? null : $_POST['setor_id'];
        $unidade_id = empty($_POST['unidade_id']) ? null : $_POST['unidade_id'];
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        // Pega o avatar atual (caso a gente precise apagar)
        $stmt_avatar = $pdo->prepare("SELECT avatar_path FROM usuarios WHERE id_usuario = ?");
        $stmt_avatar->execute([$id_usuario]);
        $avatar_atual = $stmt_avatar->fetchColumn();
        
        $avatar_db_path = $avatar_atual; // Começa com o valor antigo

        // --- LÓGICA DE PROCESSAMENTO DO AVATAR ---

        // 1. Se o usuário marcou "Remover foto"
        if (isset($_POST['remover_avatar'])) {
            if ($avatar_atual && file_exists($upload_dir . $avatar_atual)) {
                unlink($upload_dir . $avatar_atual); // Apaga o arquivo físico
            }
            $avatar_db_path = null; // Salva NULL no banco
        
        // 2. Se o usuário enviou uma NOVA foto
        } elseif (isset($_FILES['avatar_upload']) && $_FILES['avatar_upload']['error'] == UPLOAD_ERR_OK) {
            
            $file_tmp = $_FILES['avatar_upload']['tmp_name'];
            $file_name_original = $_FILES['avatar_upload']['name'];
            $file_size = $_FILES['avatar_upload']['size'];
            $file_ext = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));
            
            // Segurança: define extensões permitidas e tamanho
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
            $max_size = 2 * 1024 * 1024; // 2 MB

            if (in_array($file_ext, $allowed_ext) && $file_size <= $max_size) {
                // Se o usuário já tinha uma foto, apaga a antiga
                if ($avatar_atual && file_exists($upload_dir . $avatar_atual)) {
                    unlink($upload_dir . $avatar_atual);
                }
                
                // Cria um nome de arquivo único (ex: user_15.png)
                $novo_nome_arquivo = "user_" . $id_usuario . "." . $file_ext;
                $caminho_final = $upload_dir . $novo_nome_arquivo;

                // Move o arquivo
                if (move_uploaded_file($file_tmp, $caminho_final)) {
                    $avatar_db_path = $novo_nome_arquivo; // Salva o novo nome no banco
                }
            } else {
                // Se o arquivo for inválido (tamanho/tipo), redireciona com erro
                header("Location: editar.php?id={$id_usuario}&erro=avatar_invalido");
                exit;
            }
        }
        // Se nem 'remover' nem 'upload' foram marcados, $avatar_db_path continua o mesmo.

        // --- Fim da Lógica do Avatar ---
        
        // Atualiza os dados principais (AGORA INCLUINDO O AVATAR_PATH)
        $sql = "UPDATE usuarios SET 
                    nome = ?, email = ?, role = ?, setor_id = ?, unidade_id = ?, ativo = ?, avatar_path = ?
                WHERE id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $role, $setor_id, $unidade_id, $ativo, $avatar_db_path, $id_usuario]);

        // ATUALIZA A SENHA (somente se uma nova foi digitada)
        if (!empty($_POST['senha'])) {
            $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $sql_pass = "UPDATE usuarios SET senha_hash = ? WHERE id_usuario = ?";
            $pdo->prepare($sql_pass)->execute([$senha_hash, $id_usuario]);
        }
        
        // Se o usuário editou o PRÓPRIO perfil, atualiza a sessão
        if ($id_usuario == $usuario_id_logado) {
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_avatar'] = $avatar_db_path;
        }

        header("Location: index.php?sucesso=editado");
        exit;

    } elseif ($acao === 'excluir_usuario' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        // --- LÓGICA DE EXCLUIR USUÁRIO ---
        // (A lógica de exclusão não precisa mudar, mas é bom apagar a foto)
        
        $id_usuario = (int)$_GET['id'];

        if ($id_usuario == $usuario_id_logado) {
             header("Location: index.php?erro=auto_delete");
             exit;
        }
        if ($id_usuario == 1) {
             header("Location: index.php?erro=admin_raiz");
             exit;
        }

        // Apaga a foto antes de apagar o usuário
        $stmt_avatar = $pdo->prepare("SELECT avatar_path FROM usuarios WHERE id_usuario = ?");
        $stmt_avatar->execute([$id_usuario]);
        $avatar_atual = $stmt_avatar->fetchColumn();
        if ($avatar_atual && file_exists($upload_dir . $avatar_atual)) {
            unlink($upload_dir . $avatar_atual);
        }

        // Exclui o usuário
        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_usuario]);

        header("Location: index.php?sucesso=excluido");
        exit;
    }

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        header("Location: index.php?erro=email_duplicado");
    } else {
        die("Erro no banco de dados: " . $e->getMessage());
    }
}

// Se nenhuma ação válida foi passada
header("Location: index.php");
exit;
?>