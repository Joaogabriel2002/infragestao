<?php
// /chamados/processar.php (ATUALIZADO)

// 1. Inclui o Header (Segurança, Conexão, Sessão)
require_once '../includes/header.php'; // Sobe um nível

// 2. Verifica se os dados vieram via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Acesso inválido.";
    header('Location: index.php');
    exit;
}

// 3. Pega a 'acao' e o 'chamado_id' (se existir)
$acao = $_POST['acao'] ?? 'nenhuma';
$chamado_id = (int)($_POST['chamado_id'] ?? 0); // Pega o ID do chamado (para ações de update)

// =======================================================
// ROTEAMENTO DA AÇÃO
// =======================================================

if ($acao === 'novo_chamado') {
    // --- LÓGICA DE NOVO CHAMADO (Existente) ---
    try {
        $titulo = $_POST['titulo'];
        $problema_relatado = $_POST['problema_relatado'];
        $categoria_id = $_POST['categoria_id'];
        $prioridade = $_POST['prioridade'];
        $ativo_id = empty($_POST['ativo_id']) ? null : $_POST['ativo_id'];
        $autor_id = $usuario_id_logado; // Vem do header

        $sql = "INSERT INTO chamados 
                    (titulo, problema_relatado, autor_id, ativo_id, categoria_id, prioridade, status_chamado, dt_abertura)
                VALUES 
                    (?, ?, ?, ?, ?, ?, 'Aberto', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $titulo,
            $problema_relatado,
            $autor_id,
            $ativo_id,
            $categoria_id,
            $prioridade
        ]);
        
        // Pega o ID do chamado que acabamos de criar
        $novo_chamado_id = $pdo->lastInsertId();

        // Redireciona para a nova página 'ver.php'
        header("Location: {$base_url}/chamados/ver.php?id={$novo_chamado_id}&status=novo");
        exit;

    } catch (PDOException $e) {
        die("Erro ao salvar o chamado: " . $e->getMessage());
    }

} elseif ($acao === 'add_comentario') {
    // --- (NOVO) LÓGICA DE ADICIONAR COMENTÁRIO ---
    try {
        $comentario = $_POST['comentario'];
        
        if (!empty($comentario) && $chamado_id > 0) {
            $sql = "INSERT INTO chamado_atualizacoes (chamado_id, autor_id, comentario, dt_atualizacao)
                    VALUES (?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$chamado_id, $usuario_id_logado, $comentario]);
        }
        
        // Redireciona de volta para a página do chamado
        header("Location: {$base_url}/chamados/ver.php?id={$chamado_id}");
        exit;

    } catch (PDOException $e) {
        die("Erro ao adicionar comentário: " . $e->getMessage());
    }

} elseif ($acao === 'update_chamado') {
    // --- (NOVO) LÓGICA DE ATUALIZAR STATUS/TÉCNICO ---
    try {
        // Coleta os dados do formulário de "Ações do Técnico"
        $status = $_POST['status'];
        $tecnico_id = $_POST['tecnico_id'];
        $solucao_aplicada = $_POST['solucao_aplicada'];
        
        // Lógica para "Assumir Chamado"
        if (isset($_POST['assumir'])) {
            $tecnico_id = $usuario_id_logado; // O técnico logado assume
            $status = 'Em Atendimento'; // Muda o status automaticamente
        }

        // Validação: Se fechar, tem que ter solução
        if ($status === 'Fechado' && empty($solucao_aplicada)) {
            // No futuro, podemos redirecionar com uma msg de erro
            die("Erro: Você não pode fechar um chamado sem uma solução aplicada.");
        }

        // Define a data de fechamento (se aplicável)
        $dt_fechamento_sql = ($status === 'Fechado') ? "dt_fechamento = NOW()" : "dt_fechamento = NULL";
        
        // Trata o ID do técnico (se for 'Não atribuído', salva NULL)
        $tecnico_id_sql = empty($tecnico_id) ? null : $tecnico_id;

        $sql = "UPDATE chamados SET 
                    status_chamado = ?,
                    tecnico_id = ?,
                    solucao_aplicada = ?,
                    $dt_fechamento_sql
                WHERE id_chamado = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $tecnico_id_sql, $solucao_aplicada, $chamado_id]);

        // Redireciona de volta para a página do chamado
        header("Location: {$base_url}/chamados/ver.php?id={$chamado_id}");
        exit;

    } catch (PDOException $e) {
        die("Erro ao atualizar chamado: " . $e->getMessage());
    }

} elseif ($acao === 'add_estoque') {
    // --- (NOVO) LÓGICA DE BAIXA DE ESTOQUE (O TONER!) ---
    
    // Isto é crucial! Usamos uma "Transação".
    // Ou os DOIS comandos funcionam (INSERT E UPDATE), ou NENHUM funciona.
    // Isso evita que você dê baixa (INSERT) mas falhe em atualizar o total (UPDATE).
    
    try {
        // Inicia a transação
        $pdo->beginTransaction();

        $modelo_id = (int)$_POST['modelo_id'];
        $quantidade = (int)$_POST['quantidade'];

        // Quantidade negativa (é uma SAÍDA)
        $quantidade_saida = -abs($quantidade); // Garante que é ex: -1

        // 1. INSERE O REGISTRO NO "LEDGER" (A auditoria)
        $sql_mov = "INSERT INTO movimentacoes_estoque 
                        (modelo_id, chamado_id, usuario_id, quantidade, tipo_movimentacao, data_movimentacao)
                    VALUES
                        (?, ?, ?, ?, 'SAIDA_CHAMADO', NOW())";
        $stmt_mov = $pdo->prepare($sql_mov);
        $stmt_mov->execute([
            $modelo_id,
            $chamado_id,
            $usuario_id_logado,
            $quantidade_saida
        ]);

        // 2. ATUALIZA O "CACHE" (O total em estoque)
        $sql_cat = "UPDATE catalogo_modelos SET 
                        quantidade_em_estoque = quantidade_em_estoque + (?)
                    WHERE id_modelo = ?";
        $stmt_cat = $pdo->prepare($sql_cat);
        $stmt_cat->execute([$quantidade_saida, $modelo_id]);
        
        // Se tudo deu certo até aqui, confirma as mudanças no banco
        $pdo->commit();

        // Redireciona de volta para a página do chamado
        header("Location: {$base_url}/chamados/ver.php?id={$chamado_id}");
        exit;

    } catch (PDOException $e) {
        // Se algo deu errado, desfaz TUDO
        $pdo->rollBack();
        die("Erro ao dar baixa no estoque (Transação revertida): " . $e->getMessage());
    }

} else {
    // Se a 'acao' não for reconhecida
    echo "Ação desconhecida.";
    header('Location: index.php');
    exit;
}
?>