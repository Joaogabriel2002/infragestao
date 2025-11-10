<?php
// /estoque/processar.php (ARQUIVO CORRETO)

// 1. Inicia a sessão (essencial para a segurança)
session_start();

// 2. Define a URL base (para o redirect funcionar)
$base_url = "/infragestao"; // Verifique se o nome da pasta está correto

// 3. Inclui APENAS a conexão (só o $pdo, sem HTML)
require_once $_SERVER['DOCUMENT_ROOT'] . $base_url . '/config/conexao.php';

// 4. Pega os dados da SESSÃO e faz a segurança
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] == 'USUARIO') {
    // Se não for Técnico ou Admin, não pode fazer nada aqui
    header("Location: {$base_url}/login.php");
    exit;
}
$usuario_id_logado = $_SESSION['usuario_id']; // O técnico que está registrando

// 5. Pega a 'acao'
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php"); // Se não for POST, volta
    exit;
}
$acao = $_POST['acao'] ?? 'nenhuma';

// =======================================================
// ROTEAMENTO DA AÇÃO
// =======================================================

if ($acao === 'nova_entrada') {
    
    // --- LÓGICA DE ENTRADA DE ESTOQUE ---
    
    // Usamos uma Transação (ou tudo funciona, ou nada funciona)
    try {
        // Inicia a transação
        $pdo->beginTransaction();

        $modelo_id = (int)$_POST['modelo_id'];
        $quantidade = (int)$_POST['quantidade'];
        
        // Trata campos opcionais
        $fornecedor_id = empty($_POST['fornecedor_id']) ? null : (int)$_POST['fornecedor_id'];
        
        // Validação básica
        if ($quantidade <= 0) {
            throw new Exception("Quantidade deve ser positiva.");
        }

        // Quantidade positiva (é uma ENTRADA)
        $quantidade_entrada = abs($quantidade); // Garante que é ex: 50

        // 1. INSERE O REGISTRO NO "LEDGER" (A auditoria)
        $sql_mov = "INSERT INTO movimentacoes_estoque 
                        (modelo_id, fornecedor_id, usuario_id, quantidade, tipo_movimentacao, data_movimentacao)
                    VALUES
                        (?, ?, ?, ?, 'ENTRADA_NF', NOW())";
        $stmt_mov = $pdo->prepare($sql_mov);
        $stmt_mov->execute([
            $modelo_id,
            $fornecedor_id,
            $usuario_id_logado,
            $quantidade_entrada
        ]);

        // 2. ATUALIZA O "CACHE" (O total em estoque)
        $sql_cat = "UPDATE catalogo_modelos SET 
                        quantidade_em_estoque = quantidade_em_estoque + (?)
                    WHERE id_modelo = ?";
        $stmt_cat = $pdo->prepare($sql_cat);
        $stmt_cat->execute([$quantidade_entrada, $modelo_id]);
        
        // Se tudo deu certo, confirma as mudanças no banco
        $pdo->commit();

        // Redireciona de volta para a página de entrada com msg de sucesso
        header("Location: {$base_url}/estoque/entrada.php?sucesso=1");
        exit;

    } catch (Exception $e) {
        // Se algo deu errado, desfaz TUDO
        $pdo->rollBack();
        // Redireciona com msg de erro
        header("Location: {$base_url}/estoque/entrada.php?erro=1");
        // die("Erro ao registrar entrada (Transação revertida): " . $e->getMessage());
    }
    
} else {
    // Se a 'acao' não for reconhecida
    echo "Ação desconhecida.";
    header("Location: {$base_url}/estoque/index.php");
    exit;
}
?>