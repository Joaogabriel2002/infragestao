<?php
// /ativos/processar.php (ATUALIZADO COM ALOCAÇÃO)

session_start();
$base_url = "/infragestao"; 
require_once $_SERVER['DOCUMENT_ROOT'] . $base_url . '/config/conexao.php';

// Segurança (existente)
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] == 'USUARIO') {
    header("Location: {$base_url}/login.php");
    exit;
}
// ... (resto da segurança) ...

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$base_url}/ativos/index.php");
    exit;
}
$acao = $_POST['acao'] ?? 'nenhuma';

// =======================================================
// ROTEAMENTO
// =======================================================

if ($acao === 'novo_ativo') {
    try {
        // Coletar dados (existente)
        $nome_ativo = $_POST['nome_ativo'];
        $modelo_id = $_POST['modelo_id'];
        $unidade_id = $_POST['unidade_id'];
        $patrimonio = empty($_POST['patrimonio']) ? null : $_POST['patrimonio'];
        $ip_address = empty($_POST['ip_address']) ? null : $_POST['ip_address'];
        $remote_id = empty($_POST['remote_id']) ? null : $_POST['remote_id'];
        $operating_system = empty($_POST['operating_system']) ? null : $_POST['operating_system'];
        
        // !! NOVOS DADOS !!
        $setor_id = empty($_POST['setor_id']) ? null : $_POST['setor_id'];
        $usuario_id = empty($_POST['usuario_id']) ? null : $_POST['usuario_id'];
        
        $status_ativo = 'Ativo'; 

        // !! SQL ATUALIZADO !!
        $sql = "INSERT INTO ativos 
                    (nome_ativo, modelo_id, unidade_id, setor_id, usuario_id, patrimonio, ip_address, remote_id, operating_system, status_ativo)
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        // !! EXECUTE ATUALIZADO !!
        $stmt->execute([
            $nome_ativo,
            $modelo_id,
            $unidade_id,
            $setor_id,
            $usuario_id,
            $patrimonio,
            $ip_address,
            $remote_id,
            $operating_system,
            $status_ativo
        ]);
        
        header("Location: {$base_url}/ativos/index.php");
        exit;
    } catch (PDOException $e) {
        die("Erro ao salvar o ativo: " . $e->getMessage());
    }

} elseif ($acao === 'editar_ativo') {
    try {
        // Coletar dados (existente)
        $id_ativo = $_POST['id_ativo'];
        $nome_ativo = $_POST['nome_ativo'];
        $modelo_id = $_POST['modelo_id'];
        $unidade_id = $_POST['unidade_id'];
        $status_ativo = $_POST['status_ativo'];
        $patrimonio = empty($_POST['patrimonio']) ? null : $_POST['patrimonio'];
        $ip_address = empty($_POST['ip_address']) ? null : $_POST['ip_address'];
        $remote_id = empty($_POST['remote_id']) ? null : $_POST['remote_id'];
        $operating_system = empty($_POST['operating_system']) ? null : $_POST['operating_system'];
        
        // !! NOVOS DADOS !!
        $setor_id = empty($_POST['setor_id']) ? null : $_POST['setor_id'];
        $usuario_id = empty($_POST['usuario_id']) ? null : $_POST['usuario_id'];

        // !! SQL ATUALIZADO !!
        $sql = "UPDATE ativos SET 
                    nome_ativo = ?, modelo_id = ?, unidade_id = ?, setor_id = ?, usuario_id = ?, 
                    patrimonio = ?, ip_address = ?, remote_id = ?, operating_system = ?, status_ativo = ?
                WHERE id_ativo = ?";
        
        $stmt = $pdo->prepare($sql);
        // !! EXECUTE ATUALIZADO !!
        $stmt->execute([
            $nome_ativo, $modelo_id, $unidade_id, $setor_id, $usuario_id,
            $patrimonio, $ip_address, $remote_id, $operating_system, $status_ativo,
            $id_ativo // O ID vai no WHERE
        ]);
        
        header("Location: {$base_url}/ativos/index.php");
        exit;
    } catch (PDOException $e) {
        die("Erro ao atualizar o ativo: " . $e->getMessage());
    }
    
} else {
    echo "Ação desconhecida.";
    header("Location: {$base_url}/ativos/index.php");
    exit;
}
?>