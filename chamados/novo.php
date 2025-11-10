<?php
// /chamados/novo.php

// 1. Inclui o Header
// $pdo, $base_url e a sessão já estão disponíveis aqui
require_once '../includes/header.php'; // Usamos .. para "subir" um nível

// --- INÍCIO DAS CONSULTAS PARA OS DROPDOWNS ---

// 2. Buscar todos os ATIVOS para o <select>
// Vamos buscar (id, nome) e também a (unidade), para ficar mais fácil de achar
$sql_ativos = "SELECT 
                    a.id_ativo, 
                    a.nome_ativo, 
                    u.nome_unidade 
                FROM ativos a
                JOIN unidades u ON a.unidade_id = u.id_unidade
                ORDER BY u.nome_unidade, a.nome_ativo";
$stmt_ativos = $pdo->query($sql_ativos);
$lista_ativos = $stmt_ativos->fetchAll();


// 3. Buscar todas as CATEGORIAS para o <select>
$sql_categorias = "SELECT id_categoria, nome_categoria FROM categorias ORDER BY nome_categoria";
$stmt_categorias = $pdo->query($sql_categorias);
$lista_categorias = $stmt_categorias->fetchAll();

// --- FIM DAS CONSULTAS ---
?>

<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6">Abrir Novo Chamado</h2>

    <form action="processar.php" method="POST">
        
        <input type="hidden" name="acao" value="novo_chamado">

        <div class="mb-4">
            <label for="titulo" class="block text-gray-700 font-semibold mb-2">Título do Chamado *</label>
            <input type="text" id="titulo" name="titulo" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" 
                   placeholder="Ex: Impressora fiscal não liga" required>
            <p class="text-sm text-gray-500 mt-1">Seja breve e direto (Ex: PC não liga, Erro no sistema X).</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            
            <div>
                <label for="ativo_id" class="block text-gray-700 font-semibold mb-2">Ativo Relacionado (Opcional)</label>
                <select id="ativo_id" name="ativo_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    
                    <option value="">[Nenhum ativo específico]</option>
                    
                    <?php foreach ($lista_ativos as $ativo): ?>
                        <option value="<?= $ativo['id_ativo'] ?>">
                            <?= htmlspecialchars($ativo['nome_unidade'] . ' - ' . $ativo['nome_ativo']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
                <p class="text-sm text-gray-500 mt-1">Selecione o equipamento que está com problema.</p>
            </div>

            <div>
                <label for="categoria_id" class="block text-gray-700 font-semibold mb-2">Categoria *</label>
                <select id="categoria_id" name="categoria_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                    
                    <option value="">[Selecione o tipo de problema]</option>

                    <?php foreach ($lista_categorias as $categoria): ?>
                        <option value="<?= $categoria['id_categoria'] ?>">
                            <?= htmlspecialchars($categoria['nome_categoria']) ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>
        </div>

        <div class="mb-4">
            <label for="prioridade" class="block text-gray-700 font-semibold mb-2">Prioridade *</label>
            <select id="prioridade" name="prioridade" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" required>
                <option value="BAIXA">Baixa (Pode aguardar)</option>
                <option value="MEDIA" selected>Média (Normal)</option>
                <option value="ALTA">Alta (Resolver rápido)</option>
                <option value="URGENTE">Urgente (Parou o setor!)</option>
            </select>
        </div>

        <div class="mb-6">
            <label for="problema_relatado" class="block text-gray-700 font-semibold mb-2">Descreva o Problema *</label>
            <textarea id="problema_relatado" name="problema_relatado" rows="6"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                      placeholder="Descreva com o máximo de detalhes o que aconteceu, o que você tentou fazer e qual a mensagem de erro (se houver)." required></textarea>
        </div>
        
        <div>
            <button type="submit" 
                    class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-300">
                Abrir Chamado
            </button>
        </div>

    </form>
</div>


<?php
// 3. Inclui o Footer
require_once '../includes/footer.php';
?>