<?php
// /chamados/processar.php (ATUALIZADO COM VALIDAÇÃO E ESTORNO)

// 1. Inicia a sessão
session_start();

// 2. Define a URL base
$base_url = "/infragestao"; // Verifique se o nome da pasta está correto

// 3. Inclui APENAS a conexão
require_once $_SERVER['DOCUMENT_ROOT'] . $base_url . '/config/conexao.php';

// 4. Pega os dados da SESSÃO
if (!isset($_SESSION['usuario_id'])) {
    header("Location: {$base_url}/login.php");
    exit;
}
$usuario_id_logado = $_SESSION['usuario_id'];
$usuario_role_logado = $_SESSION['usuario_role'];

// 5. Pega a 'acao' (POST ou GET)
$acao = $_POST['acao'] ?? $_GET['acao'] ?? 'nenhuma';
$chamado_id = (int)($_POST['chamado_id'] ?? $_GET['id'] ?? 0); // Pega o ID de qualquer forma

// =======================================================
// ROTEAMENTO DA AÇÃO
// =======================================================

// Ação de 'novo_chamado' (sem mudanças)
if ($acao === 'novo_chamado' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $titulo = $_POST['titulo'];
        $problema_relatado = $_POST['problema_relatado'];
        $categoria_id = $_POST['categoria_id'];
        $prioridade = $_POST['prioridade'];
        $ativo_id = empty($_POST['ativo_id']) ? null : $_POST['ativo_id'];
        $autor_id = $usuario_id_logado; 

        $sql = "INSERT INTO chamados (titulo, problema_relatado, autor_id, ativo_id, categoria_id, prioridade, status_chamado, dt_abertura) VALUES (?, ?, ?, ?, ?, ?, 'Aberto', NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titulo, $problema_relatado, $autor_id, $ativo_id, $categoria_id, $prioridade]);
        
        $novo_chamado_id = $pdo->lastInsertId();
        header("Location: {$base_url}/chamados/ver.php?id={$novo_chamado_id}&sucesso=novo");
        exit;
    } catch (PDOException $e) {
        die("Erro ao salvar o chamado: " . $e->getMessage());
    }

// Ação de 'add_comentario' (sem mudanças)
} elseif ($acao === 'add_comentario' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $comentario = $_POST['comentario'];
        if (!empty($comentario) && $chamado_id > 0) {
            $sql = "INSERT INTO chamado_atualizacoes (chamado_id, autor_id, comentario, dt_atualizacao) VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$chamado_id, $usuario_id_logado, $comentario]);
        }
        header("Location: {$base_url}/chamados/ver.php?id={$chamado_id}");
        exit;
    } catch (PDOException $e) {
        die("Erro ao adicionar comentário: " . $e->getMessage());
    }

// Ação de 'update_chamado' (sem mudanças)
} elseif ($acao === 'update_chamado' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $status = $_POST['status'];
        $tecnico_id = $_POST['tecnico_id'];
        $solucao_aplicada = $_POST['solucao_aplicada'];
        
        if (isset($_POST['assumir'])) {
            $tecnico_id = $usuario_id_logado; 
            $status = 'Em Atendimento'; 
        }

        if ($status === 'Fechado' && empty($solucao_aplicada)) {
            die("Erro: Você não pode fechar um chamado sem uma solução aplicada.");
        }

        $dt_fechamento_sql = ($status === 'Fechado') ? "dt_fechamento = NOW()" : "dt_fechamento = NULL";
        $tecnico_id_sql = empty($tecnico_id) ? null : $tecnico_id;

        $sql = "UPDATE chamados SET status_chamado = ?, tecnico_id = ?, solucao_aplicada = ?, $dt_fechamento_sql WHERE id_chamado = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $tecnico_id_sql, $solucao_aplicada, $chamado_id]);
        
        header("Location: {$base_url}/chamados/ver.php?id={$chamado_id}");
        exit;
    } catch (PDOException $e) {
        die("Erro ao atualizar chamado: " . $e->getMessage());
    }

// Ação 'add_estoque' (COM VALIDAÇÃO ANTI-NEGATIVO)
} elseif ($acao === 'add_estoque' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        $pdo->beginTransaction();

        $modelo_id = (int)$_POST['modelo_id'];
        $quantidade = (int)$_POST['quantidade'];
        $quantidade_saida_abs = abs($quantidade); // Ex: 1

        // =======================================================
        // !! NOVA VALIDAÇÃO (ANTI-NEGATIVO) !!
        // =======================================================
        // 1. Busca o estoque atual ANTES de tentar subtrair
        $stmt_check = $pdo->prepare("SELECT quantidade_em_estoque FROM catalogo_modelos WHERE id_modelo = ?");
        $stmt_check->execute([$modelo_id]);
        $estoque_atual = (int)$stmt_check->fetchColumn();

        // 2. Verifica se a baixa deixará o estoque negativo
        if ($estoque_atual < $quantidade_saida_abs) {
            // Se for negativo, reverte tudo e redireciona com erro
            $pdo->rollBack();
            header("Location: {$base_url}/chamados/ver.php?id={$chamado_id}&erro=estoque_insuficiente");
            exit;
        }
        // =======================================================
        // FIM DA VALIDAÇÃO
        // =======================================================

        // Se passou na validação, continua a baixa
        $quantidade_saida_db = -abs($quantidade); // Ex: -1

        // 1. INSERE O REGISTRO NO "LEDGER"
        $sql_mov = "INSERT INTO movimentacoes_estoque (modelo_id, chamado_id, usuario_id, quantidade, tipo_movimentacao, data_movimentacao) VALUES (?, ?, ?, ?, 'SAIDA_CHAMADO', NOW())";
        $stmt_mov = $pdo->prepare($sql_mov);
        $stmt_mov->execute([$modelo_id, $chamado_id, $usuario_id_logado, $quantidade_saida_db]);

        // 2. ATUALIZA O "CACHE"
        $sql_cat = "UPDATE catalogo_modelos SET quantidade_em_estoque = quantidade_em_estoque + (?) WHERE id_modelo = ?";
        $stmt_cat = $pdo->prepare($sql_cat);
        $stmt_cat->execute([$quantidade_saida_db, $modelo_id]);
        
        $pdo->commit();
        header("Location: {$base_url}/chamados/ver.php?id={$chamado_id}&sucesso=add_estoque");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Erro ao dar baixa no estoque: " . $e->getMessage());
    }

// =======================================================
// !! NOVA AÇÃO (ESTORNO / DESVINCULAR) !!
// =======================================================
} elseif ($acao === 'remover_estoque' && $_SERVER['REQUEST_METHOD'] === 'GET') {

    try {
        $pdo->beginTransaction();
        
        $movimentacao_id = (int)$_GET['mov_id'];

        // 1. Busca os dados da movimentação (qual item e qual quantidade foi baixada)
        $stmt_mov = $pdo->prepare("SELECT modelo_id, quantidade FROM movimentacoes_estoque WHERE id_movimentacao = ?");
        $stmt_mov->execute([$movimentacao_id]);
        $mov = $stmt_mov->fetch();

        if (!$mov) {
            throw new Exception("Movimentação não encontrada.");
        }

        // A quantidade no banco é negativa (ex: -1). 
        // Precisamos do valor absoluto para REPOR (ex: 1)
        $quantidade_a_repor = abs($mov['quantidade']);
        $modelo_id = $mov['modelo_id'];

        // 2. DELETA o registro do "Ledger"
        $stmt_del = $pdo->prepare("DELETE FROM movimentacoes_estoque WHERE id_movimentacao = ?");
        $stmt_del->execute([$movimentacao_id]);

        // 3. ATUALIZA O "CACHE" (Devolve o item ao estoque)
        $sql_cat = "UPDATE catalogo_modelos SET quantidade_em_estoque = quantidade_em_estoque + (?) WHERE id_modelo = ?";
        $stmt_cat = $pdo->prepare($sql_cat);
        $stmt_cat->execute([$quantidade_a_repor, $modelo_id]);

        $pdo->commit();
        header("Location: {$base_url}/chamados/ver.php?id={$chamado_id}&sucesso=removido");
        exit;
    
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Erro ao estornar item: " . $e->getMessage());
    }

} else {
    // Ação desconhecida
    echo "Ação desconhecida.";
    header("Location: {$base_url}/index.php");
    exit;
}
?>