<?php
// /admin/usuarios/processar.php (ARQUIVO NOVO)

// 1. Inicia a sessão (essencial para a segurança)
session_start();

// 2. Define a URL base (para o redirect funcionar)
$base_url = "/infragestao"; // Verifique se o nome da pasta está correto

// 3. Inclui APENAS a conexão (só o $pdo, sem HTML)
require_once $_SERVER['DOCUMENT_ROOT'] . $base_url . '/config/conexao.php';

// 4. Pega os dados da SESSÃO e faz a segurança
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] != 'ADMIN') {
    // Se não for Admin, não pode fazer nada aqui
    header("Location: {$base_url}/login.php");
    exit;
}
$usuario_id_logado = $_SESSION['usuario_id'];

// 5. Pega a 'acao'
// (Usamos GET para exclusão, POST para criação/edição)
$acao = $_POST['acao'] ?? $_GET['acao'] ?? 'nenhuma';

// =======================================================
// ROTEAMENTO DA AÇÃO
// =======================================================

try {
    if ($acao === 'novo_usuario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // --- LÓGICA DE NOVO USUÁRIO ---
        
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $role = $_POST['role'];
        $setor_id = empty($_POST['setor_id']) ? null : $_POST['setor_id'];
        $unidade_id = empty($_POST['unidade_id']) ? null : $_POST['unidade_id'];
        
        // Validação
        if (empty($nome) || empty($email) || empty($senha) || empty($role)) {
             header("Location: index.php?erro=campos_vazios");
             exit;
        }

        // Criptografa a senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nome, email, senha_hash, role, setor_id, unidade_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $senha_hash, $role, $setor_id, $unidade_id]);
        
        header("Location: index.php?sucesso=novo");
        exit;

    } elseif ($acao === 'editar_usuario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // --- LÓGICA DE EDITAR USUÁRIO ---
        
        $id_usuario = $_POST['id_usuario'];
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $setor_id = empty($_POST['setor_id']) ? null : $_POST['setor_id'];
        $unidade_id = empty($_POST['unidade_id']) ? null : $_POST['unidade_id'];
        $ativo = isset($_POST['ativo']) ? 1 : 0; // Checkbox

        // Atualiza os dados principais
        $sql = "UPDATE usuarios SET 
                    nome = ?, email = ?, role = ?, setor_id = ?, unidade_id = ?, ativo = ?
                WHERE id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $email, $role, $setor_id, $unidade_id, $ativo, $id_usuario]);

        // ATUALIZA A SENHA (somente se uma nova foi digitada)
        if (!empty($_POST['senha'])) {
            $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $sql_pass = "UPDATE usuarios SET senha_hash = ? WHERE id_usuario = ?";
            $pdo->prepare($sql_pass)->execute([$senha_hash, $id_usuario]);
        }
        
        header("Location: index.php?sucesso=editado");
        exit;

    } elseif ($acao === 'excluir_usuario' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        // --- LÓGICA DE EXCLUIR USUÁRIO ---
        
        $id_usuario = (int)$_GET['id'];

        // Regra de segurança: NÃO DEIXE O USUÁRIO SE EXCLUIR
        if ($id_usuario == $usuario_id_logado) {
             header("Location: index.php?erro=auto_delete");
             exit;
        }
        
        // Regra de segurança 2: Não exclua o Admin "raiz" (ID 1, por exemplo)
        // (Vamos presumir que o primeiro admin é o ID 1)
        if ($id_usuario == 1) {
             header("Location: index.php?erro=admin_raiz");
             exit;
        }

        // OK, pode excluir
        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_usuario]);

        header("Location: index.php?sucesso=excluido");
        exit;
    }

} catch (PDOException $e) {
    // Trata erro de email duplicado
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