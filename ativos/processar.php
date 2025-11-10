<?php
// /ativos/processar.php (CORRIGIDO e ATUALIZADO para EDITAR)

// 1. Inicia a sessão (essencial para a segurança)
session_start();

// 2. Define a URL base (para o redirect funcionar)
// !! IMPORTANTE !! Verifique se o nome da sua pasta é 'infragestao'
$base_url = "/infragestao"; 

// 3. Inclui APENAS a conexão (só o $pdo, sem HTML)
require_once $_SERVER['DOCUMENT_ROOT'] . $base_url . '/config/conexao.php';

// 4. Pega os dados da SESSÃO e faz a segurança
if (!isset($_SESSION['usuario_id'])) {
    header("Location: {$base_url}/login.php");
    exit;
}
$usuario_id_logado = $_SESSION['usuario_id'];
$usuario_role_logado = $_SESSION['usuario_role'];

// 5. Segurança: Verifica se é POST e se é Admin/Técnico
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $usuario_role_logado == 'USUARIO') {
    echo "Acesso inválido.";
    header("Location: {$base_url}/ativos/index.php");
    exit;
}

// 6. Pega a 'acao'
$acao = $_POST['acao'] ?? 'nenhuma';

// =======================================================
// ROTEAMENTO DA AÇÃO
// =======================================================

if ($acao === 'novo_ativo') {

    // --- Lógica de Novo Ativo ---

    try {
        // 1. Coletar os dados do formulário
        $nome_ativo = $_POST['nome_ativo'];
        $modelo_id = $_POST['modelo_id'];
        $unidade_id = $_POST['unidade_id'];
        
        // 2. Tratar campos opcionais (se vierem vazios, salvar NULL)
        $patrimonio = empty($_POST['patrimonio']) ? null : $_POST['patrimonio'];
        $ip_address = empty($_POST['ip_address']) ? null : $_POST['ip_address'];
        $remote_id = empty($_POST['remote_id']) ? null : $_POST['remote_id'];
        $operating_system = empty($_POST['operating_system']) ? null : $_POST['operating_system'];
        
        // Status padrão ao criar
        $status_ativo = 'Ativo'; 

        // 3. Preparar o SQL
        $sql = "INSERT INTO ativos 
                    (nome_ativo, modelo_id, unidade_id, patrimonio, ip_address, remote_id, operating_system, status_ativo)
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?)";
        
        // 4. Executar
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nome_ativo,
            $modelo_id,
            $unidade_id,
            $patrimonio,
            $ip_address,
            $remote_id,
            $operating_system,
            $status_ativo
        ]);
        
        // 5. Redirecionar para a lista de ativos
        header("Location: {$base_url}/ativos/index.php");
        exit;

    } catch (PDOException $e) {
        // Em caso de erro (ex: patrimônio duplicado), mostre o erro
        die("Erro ao salvar o ativo: " . $e->getMessage());
    }

} elseif ($acao === 'editar_ativo') {
    
    // --- (NOVO) LÓGICA DE EDITAR ATIVO ---

    try {
        // 1. Coletar os dados do formulário
        $id_ativo = $_POST['id_ativo']; // O ID do ativo que estamos editando
        $nome_ativo = $_POST['nome_ativo'];
        $modelo_id = $_POST['modelo_id'];
        $unidade_id = $_POST['unidade_id'];
        $status_ativo = $_POST['status_ativo']; // Status (Ativo, Baixado, etc.)

        // 2. Tratar campos opcionais
        $patrimonio = empty($_POST['patrimonio']) ? null : $_POST['patrimonio'];
        $ip_address = empty($_POST['ip_address']) ? null : $_POST['ip_address'];
        $remote_id = empty($_POST['remote_id']) ? null : $_POST['remote_id'];
        $operating_system = empty($_POST['operating_system']) ? null : $_POST['operating_system'];

        // 3. Preparar o SQL de UPDATE
        $sql = "UPDATE ativos SET 
                    nome_ativo = ?,
                    modelo_id = ?,
                    unidade_id = ?,
                    patrimonio = ?,
                    ip_address = ?,
                    remote_id = ?,
                    operating_system = ?,
                    status_ativo = ?
                WHERE id_ativo = ?";
        
        // 4. Executar
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nome_ativo,
            $modelo_id,
            $unidade_id,
            $patrimonio,
            $ip_address,
            $remote_id,
            $operating_system,
            $status_ativo,
            $id_ativo // O ID vai no WHERE
        ]);
        
        // 5. Redirecionar de volta para a lista de ativos
        header("Location: {$base_url}/ativos/index.php");
        exit;

    } catch (PDOException $e) {
        // Em caso de erro (ex: patrimônio duplicado), mostre o erro
        die("Erro ao atualizar o ativo: " . $e->getMessage());
    }
    
} else {
    // Se a 'acao' não for reconhecida
    echo "Ação desconhecida.";
    header("Location: {$base_url}/ativos/index.php");
    exit;
}
?>