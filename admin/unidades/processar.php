<?php
// 1. Inclui a conexão (subindo um nível)
require_once '../config/conexao.php';

// 2. LÓGICA DE ADICIONAR (via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
    
    // Coletar dados do formulário
    $nome_unidade = $_POST['nome_unidade'];
    $tipo_unidade = $_POST['tipo_unidade'];
    $endereco = $_POST['endereco'];

    // Validar (simples)
    if (empty($nome_unidade)) {
        die("O nome da unidade é obrigatório.");
    }

    // Preparar o SQL (contra SQL Injection)
    $sql = "INSERT INTO unidades (nome_unidade, tipo_unidade, endereco) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([$nome_unidade, $tipo_unidade, $endereco]);
        
        // Redirecionar de volta para o index.php da pasta unidades
        header("Location: index.php?status=sucesso");
        exit;
    } catch (PDOException $e) {
        die("Erro ao cadastrar unidade: " . $e->getMessage());
    }
}


// 3. LÓGICA DE EXCLUIR (via GET)
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {

    $id_unidade = $_GET['id'];

    $sql = "DELETE FROM unidades WHERE id_unidade = ?";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([$id_unidade]);
        
        header("Location: index.php?status=excluido");
        exit;
    } catch (PDOException $e) {
        // Tratar o erro de restrição de chave estrangeira (FK)
        if ($e->getCode() == '23000') {
            // Código 23000 = violação de integridade (não pode excluir)
            header("Location: index.php?status=erro_fk");
        } else {
            die("Erro ao excluir unidade: " . $e->getMessage());
        }
    }
}

// Se nenhuma ação válida for encontrada, redireciona
header("Location: index.php");
exit;
?>